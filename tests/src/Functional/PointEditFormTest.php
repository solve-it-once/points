<?php

namespace Drupal\Tests\points\Functional;

use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\points\Entity\Point;
use Drupal\points\Entity\PointInterface;
use Drupal\Tests\BrowserTestBase;
use Drupal\user\UserInterface;

/**
 * Tests saving points through the user edit form and the point edit form.
 *
 * The point "state" base field is the edit lock checked by the PointState
 * constraint. It has no storage, so it only carries a value when the form
 * round-trips it, so the field must stay in the point form display.
 *
 * @group points
 */
class PointEditFormTest extends BrowserTestBase {

  /**
   * The PointState constraint message.
   */
  protected const LOCK_MESSAGE = 'The point has either been modified by another user, or you have already submitted modifications. As a result, your changes cannot be saved.';

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['field', 'user', 'inline_entity_form', 'points'];

  /**
   * An administrator who edits other users and their points.
   *
   * @var \Drupal\user\UserInterface
   */
  protected UserInterface $admin;

  /**
   * The user whose points are edited.
   *
   * @var \Drupal\user\UserInterface
   */
  protected UserInterface $member;

  /**
   * The member's point entity.
   *
   * @var \Drupal\points\Entity\PointInterface
   */
  protected PointInterface $point;

  /**
   * Movements recorded before the test acts, which the assertions ignore.
   *
   * @var int
   */
  protected int $baselineMovements;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    // Mirror a typical site: a points field on users, edited inline.
    FieldStorageConfig::create([
      'field_name' => 'field_points',
      'entity_type' => 'user',
      'type' => 'entity_reference',
      'cardinality' => 1,
      'settings' => ['target_type' => 'point'],
    ])->save();
    FieldConfig::create([
      'field_name' => 'field_points',
      'entity_type' => 'user',
      'bundle' => 'user',
      'label' => 'Points',
      'settings' => [
        'handler' => 'default:point',
        'handler_settings' => ['target_bundles' => ['default' => 'default']],
      ],
    ])->save();
    \Drupal::service('entity_display.repository')
      ->getFormDisplay('user', 'user', 'default')
      ->setComponent('field_points', [
        'type' => 'inline_entity_form_simple',
        'settings' => ['form_mode' => 'default'],
      ])
      ->save();

    $this->point = Point::create(['type' => 'default', 'points' => 10]);
    $this->point->save();
    $this->member = $this->drupalCreateUser();
    $this->member->set('field_points', $this->point)->save();
    $this->baselineMovements = count($this->movementDeltas());

    $this->admin = $this->drupalCreateUser([
      'administer users',
      'administer point entities',
      'view point entities',
      'edit point entities',
    ]);
    $this->drupalLogin($this->admin);
  }

  /**
   * Saving another user's edit form without touching points succeeds.
   */
  public function testUserFormSaveWithoutPointChange(): void {
    $this->drupalGet($this->member->toUrl('edit-form'));
    $this->submitForm([], 'Save');

    $this->assertSession()->pageTextNotContains(self::LOCK_MESSAGE);
    $this->assertSession()->pageTextContains('The changes have been saved.');
    $this->assertSame('10.00', $this->reloadPoint()->getPoints());
    $this->assertSame([], $this->movementDeltas());
  }

  /**
   * Changing points on the user edit form saves them and logs one movement.
   */
  public function testUserFormSaveWithPointChange(): void {
    $this->drupalGet($this->member->toUrl('edit-form'));
    $this->submitForm([
      'field_points[0][inline_entity_form][points][0][value]' => 25,
    ], 'Save');

    $this->assertSession()->pageTextNotContains(self::LOCK_MESSAGE);
    $this->assertSession()->pageTextContains('The changes have been saved.');
    $this->assertSame('25.00', $this->reloadPoint()->getPoints());
    $this->assertSame([15.0], $this->movementDeltas());
  }

  /**
   * The lock rejects a point whose state no longer matches its saved points.
   *
   * This exercises the constraint the user edit form relies on. It is tested
   * at the entity level because the inline form re-fills the state from the
   * point as loaded for that request, so a change made between loading and
   * submitting the user edit form is not detected through the UI.
   */
  public function testLockRejectsStaleState(): void {
    $point = $this->reloadPoint();
    $point->set('state', $point->getPoints())->setPoints(30);
    $this->assertCount(0, $point->validate(), 'A current state passes.');

    $this->changePointsInBackground(12);
    $messages = [];
    foreach ($point->validate() as $violation) {
      $messages[] = (string) $violation->getMessage();
    }
    $this->assertSame([self::LOCK_MESSAGE], $messages);
  }

  /**
   * Changing points on the standalone point edit form saves them.
   */
  public function testPointFormSave(): void {
    $this->drupalGet($this->point->toUrl('edit-form'));
    $this->submitForm(['points[0][value]' => 7], 'Save');

    $this->assertSession()->pageTextNotContains(self::LOCK_MESSAGE);
    $this->assertSame('7.00', $this->reloadPoint()->getPoints());
    $this->assertSame([-3.0], $this->movementDeltas());
  }

  /**
   * The standalone point edit form also rejects a stale save.
   */
  public function testPointFormLockRejectsStaleSave(): void {
    $this->drupalGet($this->point->toUrl('edit-form'));
    $this->changePointsInBackground(12);

    $this->submitForm(['points[0][value]' => 30], 'Save');

    $this->assertSession()->pageTextContains(self::LOCK_MESSAGE);
    $this->assertSame('12.00', $this->reloadPoint()->getPoints());
    $this->assertSame([2.0], $this->movementDeltas());
  }

  /**
   * Changes the member's points through the API, as another process would.
   */
  protected function changePointsInBackground(int $points): void {
    $point = $this->reloadPoint();
    $point->setPoints($points)->save();
  }

  /**
   * Reloads the member's point entity from storage.
   */
  protected function reloadPoint(): PointInterface {
    return \Drupal::entityTypeManager()
      ->getStorage('point')
      ->loadUnchanged($this->point->id());
  }

  /**
   * Returns the deltas of the point movements recorded for the member.
   *
   * @return float[]
   *   The movement deltas, oldest first, after the setUp() baseline.
   */
  protected function movementDeltas(): array {
    $storage = \Drupal::entityTypeManager()->getStorage('point_movement');
    $ids = $storage->getQuery()
      ->accessCheck(FALSE)
      ->condition('point_id', $this->point->id())
      ->sort('mid')
      ->execute();
    $deltas = [];
    foreach ($storage->loadMultiple($ids) as $movement) {
      $deltas[] = (float) $movement->get('points')->value;
    }
    return array_slice($deltas, $this->baselineMovements ?? 0);
  }

}
