<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * A two column layout for the econcordia theme.
 *
 * @package   theme_econcordia
 * @copyright 2016 Damyon Wiese
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

user_preference_allow_ajax_update('drawer-open-nav', PARAM_ALPHA);
require_once($CFG->libdir . '/behat/lib.php');
require('courseimage.php');

// EC: Import format tiles inline color
require($CFG->dirroot .'/course/format/tiles/classes/output/inline_css_output.php');

if (isloggedin()) {
    $navdraweropen = (get_user_preferences('drawer-open-nav', 'true') == 'true');
} else {
    $navdraweropen = false;
}
$extraclasses = [];
if ($navdraweropen) {
    $extraclasses[] = 'drawer-open-left';
}
$bodyattributes = $OUTPUT->body_attributes($extraclasses);
$blockshtml = $OUTPUT->blocks('side-pre');
$hasblocks = strpos($blockshtml, 'data-block=') !== false;
$buildregionmainsettings = !$PAGE->include_region_main_settings_in_header_actions();
// If the settings menu will be included in the header then don't add it here.
$regionmainsettingsmenu = $buildregionmainsettings ? $OUTPUT->region_main_settings_menu() : false;
$templatecontext = [
    'sitename' => format_string($SITE->shortname, true, ['context' => context_course::instance(SITEID), "escape" => false]),
    'output' => $OUTPUT,
    'sidepreblocks' => $blockshtml,
    'hasblocks' => $hasblocks,
    'bodyattributes' => $bodyattributes,
    'navdraweropen' => $navdraweropen,
    'regionmainsettingsmenu' => $regionmainsettingsmenu,
    'hasregionmainsettingsmenu' => !empty($regionmainsettingsmenu)
];

$nav = $PAGE->flatnav;
$templatecontext['flatnavigation'] = $nav;
$templatecontext['firstcollectionlabel'] = $nav->get_collectionlabel();

// EC: Get course image and short name for Course banner
$templatecontext['course_image'] = $contentimages;
$templatecontext['course_shortname'] = $COURSE->shortname;
$templatecontext['course_fullname'] = $COURSE->fullname;

// EC: If lesson page, Lesson title and if isListView
$templatecontext['is_lesson'] = false;
$templatecontext['lesson_title'] = "";
$templatecontext['is_list_view'] = false;

// EC: If activity page
$templatecontext['is_activity'] = false;
if ($PAGE->activityname != '') {
    $templatecontext['is_activity'] = true;

    // EC: Do it here only when it's an activity otherwise it's throwing warnings on Dashboard
    // EC: Inline CSS may be required for Activity page
    $courseformat = course_get_format($COURSE);
    $course = $courseformat->get_course();
    $templateable = new \format_tiles\output\inline_css_output($course, false, false, false);
    $data = $templateable->export_for_template($this);
    echo $OUTPUT->render_from_template('format_tiles/inline-css', $data);
}

// EC: If we are in a Lesson, the banner will be different
if (isset($_GET['section'])) {
    $templatecontext['is_lesson'] = true;
    $pagetitle = $PAGE->title;
    $pagetitle = explode("Tile:", $pagetitle);
    $templatecontext['lesson_title'] = $pagetitle[1];

    // Check if Lesson page is in List view from Custom Fields
    $coursemetadata = get_course_metadata($COURSE->id);
    if (isset($coursemetadata['listviewlesson'])) {
        $templatecontext['is_list_view'] = true;
    }
}

// EC: Get unread messages from forums
$unreadcount = \core_message\api::count_unread_conversations($USER);
$templatecontext['unreadcount'] = $unreadcount;


// EC: If course homepage
$templatecontext['is_coursehomepage'] = false;
if (strpos($PAGE->title,$COURSE->fullname) && !$templatecontext['is_lesson']) {
    $templatecontext['is_coursehomepage'] = true;
}

echo $OUTPUT->render_from_template('theme_econcordia/columns2', $templatecontext);

