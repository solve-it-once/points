<?php

namespace Drupal\points\Tests;

use Drupal\points\Entity\Point;
use Drupal\Tests\examples\Functional\ExamplesBrowserTestBase;

/**
 * Tests the basic functions of the Points Module.
 *
 * @package Drupal\points\Tests
 *
 * @ingroup points
 *
 * @group points
 * @group examples
 */
class PointsTest extends ExamplesBrowserTestBase {

  public static $modules = array('points', 'block', 'field_ui');

  /**
   * Basic tests for Points.
   */
  public function testPoints() {
    $assert = $this->assertSession();

    $web_user = $this->drupalCreateUser(array(
      'add point entity',
      'edit point entity',
      'view point entity',
      'delete point entity',
      'administer point entity',
      'administer points_point display',
      'administer points_point fields',
      'administer points_point form display',
    ));

    // Anonymous User should not see the link to the listing.
    $assert->pageTextNotContains('Points: Points Listing');

    $this->drupalLogin($web_user);

    // Web_user user has the right to view listing.
    $assert->linkExists('Points: Points Listing');

    $this->clickLink('Points: Points Listing');

    // WebUser can add entity content.
    $assert->linkExists('Add Point');

    $this->clickLink(t('Add Point'));


    $user_ref = $web_user->name->value . ' (' . $web_user->id() . ')';

    $this->drupalPostForm(NULL, $edit, t('Save'));

    // Entity listed.
    $assert->linkExists('Edit');
    $assert->linkExists('Delete');


    // Entity shown.
    $assert->linkExists('Add Point');
    $assert->linkExists('Edit');
    $assert->linkExists('Delete');

    // Delete the entity.
    $this->clickLink('Delete');

    // Confirm deletion.
    $assert->linkExists('Cancel');
    $this->drupalPostForm(NULL, array(), 'Delete');

    // Back to list, must be empty.

    // Settings page.
    $this->drupalGet('admin/structure/points_point_settings');
    $assert->pageTextContains('Point Settings');

    // Make sure the field manipulation links are available.
    $assert->linkExists('Settings');
    $assert->linkExists('Manage fields');
    $assert->linkExists('Manage form display');
    $assert->linkExists('Manage display');
  }

  /**
   * Test all paths exposed by the module, by permission.
   */
  public function testPaths() {
    $assert = $this->assertSession();

    // Generate a point so that we can test the paths against it.
    $point = Point::create(
      array(
      )
    );
    $point->save();

    // Gather the test data.
    $data = $this->providerTestPaths($point->id());

    // Run the tests.
    foreach ($data as $datum) {
      // drupalCreateUser() doesn't know what to do with an empty permission
      // array, so we help it out.
      if ($datum[2]) {
        $user = $this->drupalCreateUser(array($datum[2]));
        $this->drupalLogin($user);
      }
      else {
        $user = $this->drupalCreateUser();
        $this->drupalLogin($user);
      }
      $this->drupalGet($datum[1]);
      $assert->statusCodeEquals($datum[0]);
    }
  }

  /**
   * Data provider for testPaths.
   *
   * @param int $point_id
   *   The id of an existing Point entity.
   *
   * @return array
   *   Nested array of testing data. Arranged like this:
   *   - Expected response code.
   *   - Path to request.
   *   - Permission for the user.
   */
  protected function providerTestPaths($point_id) {
    return array(
      array(
        200,
        '/points_point/' . $point_id,
        'view point entity',
      ),
      array(
        403,
        '/points_point/' . $point_id,
        '',
      ),
      array(
        200,
        '/points_point/list',
        'view point entity',
      ),
      array(
        403,
        '/points_point/list',
        '',
      ),
      array(
        200,
        '/points_point/add',
        'add point entity',
      ),
      array(
        403,
        '/points_point/add',
        '',
      ),
      array(
        200,
        '/points_point/' . $point_id . '/edit',
        'edit point entity',
      ),
      array(
        403,
        '/points_point/' . $point_id . '/edit',
        '',
      ),
      array(
        200,
        '/point/' . $point_id . '/delete',
        'delete point entity',
      ),
      array(
        403,
        '/point/' . $point_id . '/delete',
        '',
      ),
      array(
        200,
        'admin/structure/points_point_settings',
        'administer point entity',
      ),
      array(
        403,
        'admin/structure/points_point_settings',
        '',
      ),
    );
  }

  /**
   * Test add new fields to the point entity.
   */
  public function testAddFields() {
    $web_user = $this->drupalCreateUser(array(
      'administer point entity',
      'administer points_point display',
      'administer points_point fields',
      'administer points_point form display',
    ));

    $this->drupalLogin($web_user);
    $entity_name = 'points_point';
    $add_field_url = 'admin/structure/' . $entity_name . '_settings/fields/add-field';
    $this->drupalGet($add_field_url);
    $edit = array(
    );

    $this->drupalPostForm(NULL, $edit, t('Save and continue'));
    $expected_path = $this->buildUrl('admin/structure/' . $entity_name . '_settings/fields/' . $entity_name . '.' . $entity_name . '.field_' . $field_name . '/storage');

    // Fetch url without query parameters.
    $current_path = strtok($this->getUrl(), '?');
    $this->assertEquals($expected_path, $current_path);
  }

}