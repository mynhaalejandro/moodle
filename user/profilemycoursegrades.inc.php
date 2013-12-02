<?php

/**
 * A related to MDL-39273 for illustration purposed: Please READ:
 * 
 * Public Profile Experimental inclusion of users overall grade for each course in the -- user's public profile page
 * 
 * -- NOTE: This code should not be used on production sites!!!!!!!! becuase depending on how your site is configured & modified -
 * -- this script could display a users grades to other students or publically on the internet.
 * -- However I've published this script because I think coding the grades in to this page in a fail safe way when someone has time 
 * -- would imporove the learner experience
 *
 * -- Note on the coding here:
 * -- I'm not suggesting a PHP include file is used like here with embedded CSS. :-(
 * -- It is just a quick & clear way for me to communiate modifications
 *
 * @package    not moodlecore but an experimental script
 * @subpackage my
 * @copyright  2013 Stanmore College
 * @author     Jago Brown <jago.brown@stanmore.ac.uk>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


//Added for Grade inclusion:
require_once($CFG->dirroot . '/grade/report/lib.php');


echo '<style type="text/css">
<!--
.coursename-enhanced-userview-notdisplayed {
	padding: 3px;
	border-bottom: 1px dashed #c8c8c8;
	border-right: 3px solid #009de0;
	width: 675px;
	display: block;
	background-color: #fefefe;
	height: 1.4em;
}

a.coursegrade-enhanced-userview {
	background-color: #e1007a;
	color: #fff;
	display: block;
	width: 126px;
	padding: 0 1px 0 4px;
	font-weight: bold;
	float: right;
	font-size: 1.0em;
}

a.coursegrade-enhanced-userview:hover {
	background-color: #9fc22a;
}
-->
</style>';


if (!isset($hiddenfields['mycourses'])) {
	
	/* TODO Enhancement : Added header/title befor list of users other courses */
	//echo html_writer::tag('div',get_string('coursesenhanceduserview', 'theme_thelearner', $user), array('id' => '', 'class' => 'name-enhanced-userview'));
	echo html_writer::tag('h4',get_string('grades', 'grades') .': ', array('id' => '', 'class' => 'name-enhanced-userview'));
    if ($mycourses = enrol_get_all_users_courses($user->id, true, NULL, 'visible DESC,sortorder ASC')) {
        $shown=0;
        $courselisting = '';
        foreach ($mycourses as $mycourse) {
        
        		/* START Overall Grade inclusion
				* The following code is included inorder to display here,
				*  the data presented on the User Grades overview report
				*  TODO use renderers e.g. echo html_writer::link( new moodle_url('/grade/report/
				*  #from grade/report/overview/lib.php */
				
					
				// Get course grade_item
				$course_item = grade_item::fetch_course_item($mycourse->id);//$course->id
					
				// Get the stored grade
				$course_grade = new grade_grade(array('itemid'=>$course_item->id, 'userid'=>$user->id));//'userid'=>$this->user->id
				$course_grade->grade_item =& $course_item;
				$finalgrade = $course_grade->finalgrade;
					
				//fn in /lib/grade/grade_grade.php
				$course_overview_grade = grade_format_gradevalue($finalgrade, $course_item, true);

				//HTML Btn link to grade/report/user/index
				//note: report link to user report uses class="action-icon" also input buttons across site have id="id_submitbutton"
				$course_grade_user_link = '<a href=' . $CFG->wwwroot . '/grade/report/user/index.php?id='.$mycourse->id.'&userid='.$user->id
				. ' class="coursegrade-enhanced-userview" id="" title="Click to view User\'s course Grades"><span class="coursegrade-enhanced-userview static">Gradebook:</span> '
						. $course_overview_grade . '</a>';

				/*END Overall Grade inclusion */
        
        
            if ($mycourse->category) {
                context_helper::preload_from_record($mycourse);
                $ccontext = context_course::instance($mycourse->id);
                $class = '';
                if ($mycourse->visible == 0) {
                    if (!has_capability('moodle/course:viewhiddencourses', $ccontext)) {
                        continue;
                    }
                    $class = 'class="dimmed"';
                }
                
                $cpage = '<a href="' . $CFG->wwwroot . '/course/view.php?id='.$mycourse->id.'&userid='.$user->id
                . '"' . $class .'title="Click to view the course page">'
                . $ccontext->get_context_name(false) . '</a>'; //$cfullname
                
                
                $courselisting .= "<div class=\"coursename-enhanced-userview-notdisplayed\" >";
               /*  $courselisting .= "<a href=\"{$CFG->wwwroot}/user/view.php?id={$user->id}&amp;course={$mycourse->id}\" $class >" . $ccontext->get_context_name(false) . "</a>, ";
                *!!!! Note: $cpage creates a link to the actual course for users.  This is becuase i think this page could become the home page for users to compliment /my
                * Becuase of this change, a button for a course stats modal should be added
                *
                */
                $courselisting .= $cpage;
                $courselisting .= $course_grade_user_link . "</div>";
            }
            $shown++;
            if($shown==50) { //increased from 20 for staff who teach many courses 
                $courselisting.= "...";
                break;
            }
        }
        //echo html_writer::tag('dt', get_string('courseprofiles'));
        //echo html_writer::tag('dd', rtrim($courselisting,', '));
        //echo html_writer::tag('dt', rtrim($courselisting,', '));
        //echo html_writer::tag('dd', $coursegrade);
        
        echo html_writer::tag('div',$courselisting, array('id' => '', 'class' => ''));
        }
}
?>