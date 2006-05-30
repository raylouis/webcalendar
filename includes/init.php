<?php
/**
 * Does various initialization tasks and includes all needed files.
 *
 * This page is included by most WebCalendar pages as the only include file.
 * This greatly simplifies the other PHP pages since they don't need to worry
 * about what files it includes.
 *
 * <b>Comments:</b>
 * The following scripts do not use this file:
 * - login.php
 * - week_ssi.php
 * - upcoming.php
 * - tools/send_reminders.php
 *
 * How to use:
 * 1. call include_once 'includes/init.php'; at the top of your script.
 * 2. call any other functions or includes not in this file that you need
 * 3. call the print_header function with proper arguments
 *
 * What gets called:
 *
 * - require_once 'includes/classes/WebCalendar.class';
 * - require_once 'includes/classes/Event.class';
 * - require_once 'includes/classes/RptEvent.class';
 * - include_once 'includes/assert.php';
 * - include_once 'includes/config.php';
 * - include_once 'includes/dbi4php.php';
 * - include_once 'includes/functions.php';
 * - include_once "includes/$user_inc";
 * - include_once 'includes/validate.php';
 * - include_once 'includes/site_extras.php';
 * - include_once 'includes/access.php';
 * - include_once 'includes/translate.php';
 *
 * Also, for month.php, day.php, week.php, week_details.php:
 * - {@link send_no_cache_header()};
 *
 * @author Craig Knudsen <cknudsen@cknudsen.com>
 * @copyright Craig Knudsen, <cknudsen@cknudsen.com>, http://www.k5n.us/cknudsen
 * @license http://www.gnu.org/licenses/gpl.html GNU GPL
 * @version $Id$
 * @package WebCalendar
 */
 
require_once 'includes/classes/WebCalendar.class';
require_once 'includes/classes/Event.class';
require_once 'includes/classes/RptEvent.class';

$WebCalendar =& new WebCalendar ( __FILE__ );

include_once 'includes/assert.php';
include_once 'includes/config.php';
include_once 'includes/dbi4php.php';
include_once 'includes/functions.php';

$WebCalendar->initializeFirstPhase();

include_once "includes/$user_inc";
include_once 'includes/validate.php';
include_once 'includes/site_extras.php';
include_once 'includes/access.php';

include_once 'includes/translate.php';

include_once 'includes/gradient.php';

$WebCalendar->initializeSecondPhase();



/**
 * Prints the HTML header and opening HTML body tag.
 *
 * @param array  $includes     Array of additional files to include referenced
 *                             from the includes directory
 * @param string $HeadX        Data to be printed inside the head tag (meta,
 *                             script, etc)
 * @param string $BodyX        Data to be printed inside the Body tag (onload
 *                             for example)
 * @param bool   $disbleCustom Do not include custom header? (useful for small
 *                             popup windows, such as color selection)
 * @param bool   $disableStyle Do not include the standard css?
 * @param bool   $disableRSS   Do not include the RSS link
 */
function print_header($includes = '', $HeadX = '', $BodyX = '',
  $disableCustom=false, $disableStyle=false, $disableRSS=false ) {
  global $APPLICATION_NAME;
  global $FONTS,$WEEKENDBG,$THFG,$THBG,$PHP_SELF;
  global $TABLECELLFG,$TODAYCELLBG,$TEXTCOLOR;
  global $POPUP_FG,$BGCOLOR,$OTHERMONTHBG;
  global $LANGUAGE, $DISABLE_POPUPS, $MENU_ENABLED, $MENU_THEME;
  global $CUSTOM_HEADER, $CUSTOM_SCRIPT,$REQUEST_URI;
  global $friendly, $DISPLAY_WEEKENDS, $DISPLAY_TASKS;
  global $bodyid, $self, $login, $browser;
  $ret = '';
  //remember this view if the file is a view_x.php script
  if ( ! strstr ( $REQUEST_URI, 'view_entry' ) )  remember_this_view ( true );
  
  //get script name for later use
  $thisPage = substr($self, strrpos($self, '/') + 1);
    
  //check the css version for cache clearing if needed
  if ( ! $disableStyle ) {
    if  ( isset ( $_COOKIE['webcalendar_csscache'] ) ) {
      $webcalendar_csscache = $_COOKIE['webcalendar_csscache'];
    } else {
      $webcalendar_csscache = 1;
      SetCookie ( 'webcalendar_csscache', $webcalendar_csscache );
    }
  }
    
  $lang = '';
  if ( ! empty ( $LANGUAGE ) )
    $lang = languageToAbbrev ( $LANGUAGE );
  if ( empty ( $lang ) )
    $lang = 'en';
      
  // Start the header & specify the charset
  // The charset is defined in the translation file
  $charset = translate ( 'charset' );
  if ( empty ( $charset ) || $charset == 'charset' ) {
  $charset = 'iso-8859-1';
  }
  $ret .= "<?xml version=\"1.0\" encoding=\"$charset\"?>\n" .
    "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\" " .
    "\"DTD/xhtml1-transitional.dtd\">\n" .
    "<html xmlns=\"http://www.w3.org/1999/xhtml\" " .
    "xml:lang=\"$lang\" lang=\"$lang\">\n" .
    "<head>\n" .
    "<meta http-equiv=\"Content-Type\" content=\"text/html; " .
    "charset=$charset\" />\n";
  $ret .= "<title>".translate($APPLICATION_NAME)."</title>\n";

  $ret .= "<script type=\"text/javascript\" src=\"includes/js/util.js\"></script>\n";
  $ret .= "<script type=\"text/javascript\" src=\"includes/js/prototype.js\"></script>\n";
  // Menu control
  if ( !empty ( $friendly ) || $disableCustom ) $MENU_ENABLED = 'N';

  // Includes needed for the top menu
  if ( $MENU_ENABLED == 'Y' ) {
    $MENU_THEME = ( ! empty ( $MENU_THEME ) && $MENU_THEME != 'none' ? 
      $MENU_THEME : 'default' );
    $ret .= '<script type="text/javascript" src="includes/menu/JSCookMenu.js">' .
      '</script>' . "\n";
    $ret .= '<script type="text/javascript" src="includes/menu/themes/' . 
     $MENU_THEME. '/theme.js"></script>' . "\n";
  }

  // Any other includes?
  if ( is_array ( $includes ) ) {
    foreach( $includes as $inc ){
      if ( $inc == 'js/popups.php' && ! empty ( $DISABLE_POPUPS ) && 
       $DISABLE_POPUPS == 'Y' ) {
       //don't load popups.php javascript if DISABLE_POPUPS
      } else {
        $ret .= '<script type="text/javascript" src="js_cacher.php?inc=' .
          $inc . '"></script>' . "\n";
      }
    }
  }

  // Do we need anything else inside the header tag?
  if ($HeadX) $ret .= $HeadX."\n";

  // Include the styles
  // Include CSS needed for the top menu
  if ( $MENU_ENABLED == 'Y' ) {  
    $ret .= '<link rel="stylesheet" type="text/css" href="includes/menu/themes/' .
      $MENU_THEME . '/theme.css" />' . "\n";
  }
  //if loading admin.php, we will not us an exrternal file because
  //we need to override the global colors and this is impossible if loading
  //external file. We will still increment the webcalendar_csscache cookie though
  if ( ! $disableStyle ) {
    if ( $thisPage == 'admin.php' || $thisPage == 'pref.php') {
      //this will always force a reload of CSS
      $webcalendar_csscache = $webcalendar_csscache . 'adminpref';
    }
    $ret .= '<link rel="stylesheet" type="text/css"  href="css_cacher.php?' . 
      $login . $webcalendar_csscache . "\" />\n";
  }

  // Add custom script/stylesheet if enabled
  if ( $CUSTOM_SCRIPT == 'Y' && ! $disableCustom ) {
    $ret .= load_template ( $login, 'S' );
  }

  // Include includes/print_styles.css as a media="print" stylesheet. When the
  // user clicks on the "Printer Friendly" link, $friendly will be non-empty,
  // including this as a normal stylesheet so they can see how it will look 
  // when printed. This maintains backwards-compatibility for browsers that 
  // don't support media="print" stylesheets
  $ret .= '<link rel="stylesheet" type="text/css"' . 
    ( empty ( $friendly ) ? ' media="print"': '' ) . 
    ' href="includes/print_styles.css" />' . "\n";

  // Add RSS feed if publishing is enabled
  if ( ! empty ( $GLOBALS['RSS_ENABLED'] ) && $GLOBALS['RSS_ENABLED'] == 'Y' &&
    $login == '__public__' ||
    ( ! empty ( $GLOBALS['USER_RSS_ENABLED'] ) &&
    $GLOBALS['USER_RSS_ENABLED'] == 'Y' ) && $disableRSS == false ) {
    $ret .= '<link rel="alternate" type="application/rss+xml" ' .
      'title="' . htmlentities ( $APPLICATION_NAME ) .
      ' [RSS 2.0]" href="rss.php';
    // TODO: single-user mode, etc.
    if ( $login != '__public__' )
      $ret .= '?user=' . $login;
    $ret .= "\" />\n";
  }

  // Link to favicon
  $ret .= '<link rel="shortcut icon" href="favicon.ico" type="image/x-icon" />' . "\n";

  // Finish the header
  $ret .= "</head>\n<body";

  // Find the filename of this page and give the <body> tag the corresponding id
  if ( isset( $bodyid[$thisPage] ) )
    $ret .= " id=\"" . $bodyid[$thisPage] . '"';

  // Add any extra parts to the <body> tag
  if ( ! empty( $BodyX ) )
    $ret .= " $BodyX";
  $ret .= ">\n";

  // Add custom header if enabled
  if ( $CUSTOM_HEADER == 'Y' && ! $disableCustom ) {
    $ret .= load_template ( $login, 'H' );
  }
  //TODO convert this to return value
  echo $ret;
  // Add the top menu if enabled
  if ( $MENU_ENABLED == 'Y' )
    include_once 'includes/menu/index.php';
}


/**
 * Prints the common trailer.
 *
 * @param bool $include_nav_links Should the standard navigation links be
 *                               included in the trailer?
 * @param bool $closeDb           Close the database connection when finished?
 * @param bool $disableCustom     Disable the custom trailer the administrator
 *                                has setup?  (This is useful for small popup
 *                                windows and pages being used in an iframe.)
 */
function print_trailer ( $include_nav_links=true, $closeDb=true,
  $disableCustom=false )
{
  global $CUSTOM_TRAILER, $c, $STARTVIEW, $DEMO_MODE, $MENU_ENABLED,
    $login, $user, $cat_id, $CATEGORIES_ENABLED, $thisyear,
    $thismonth, $thisday, $DATE_FORMAT_MY, $WEEK_START, $DATE_FORMAT_MD,
    $readonly, $is_admin, $PUBLIC_ACCESS, $PUBLIC_ACCESS_CAN_ADD,
    $single_user, $use_http_auth, $login_return_path, $REQUIRE_APPROVALS,
    $is_nonuser_admin, $PUBLIC_ACCESS_OTHERS, $ALLOW_VIEW_OTHER,
    $views, $REPORTS_ENABLED, $LAYER_STATUS, $NONUSER_ENABLED,
    $GROUPS_ENABLED, $fullname, $has_boss, $is_nonuser, 
    $DISPLAY_TASKS, $DISPLAY_TASKS_IN_GRID, $MENU_DATE_TOP;
  
  $ret = '';

  if ( $include_nav_links  ) {//TODO Add test for $MENU_ENABLED == 'N'
    if ( $MENU_ENABLED == 'N' || $MENU_DATE_TOP == 'N' ) {
      $ret .= '<div id="trailer">';
      $ret .= print_menu_dates ();
      $ret .= '</div>';
    }
    include_once 'includes/trailer.php';
  }
  if ( ! empty ( $tret ) ) $ret .= $tret; //data from trailer

  // Add custom trailer if enabled
  if ( $CUSTOM_TRAILER == 'Y' && ! $disableCustom && isset ( $c ) ) {
    $ret .= load_template ( $login, 'T' );
  }

  if ( $closeDb ) {
    if ( isset ( $c ) )
      dbi_close ( $c );
    unset ( $c );
  }
 // adds an easy link to validate the pages
 if ( $DEMO_MODE == 'Y' ) {
     $ret .= '<p><a href="http://validator.w3.org/check?uri=referer"><img ' .
        'src="http://www.w3.org/Icons/valid-xhtml10" ' .
       'alt="Valid XHTML 1.0!" class="valid"  /></a></p>';
 }

 return $ret;  
}

function print_menu_dates ( $menu=false) {
  global $user, $login, $cat_id, $CATEGORIES_ENABLED, $thisyear,
    $thismonth,  $DATE_FORMAT_MY, $DATE_FORMAT_MD, $WEEK_START, $DISPLAY_WEEKENDS;
  $ret = '';
  if ( access_can_view_page ( 'month.php' ) ) {
    $monthUrl = 'month.php';
    $urlArgs = '';
  } else {
    $monthUrl = $GLOBALS['STARTVIEW'];
    if ( preg_match ( "/[?&](\S+)=(\S+)/", $monthUrl, $match ) ) {
      $monthUrl = $match[0];
      $urlArgs = "<input type=\"hidden\" name=\"$match[1]\" value=\"$match[2]\" />\n";
    }
  }
  $formclass = ( $menu == true ? 'monthmenu' : 'monthform' );
  $ret .= "<form action=\"$monthUrl\" method=\"get\" name=\"SelectMonth\" id=\"" . 
    $formclass . "\">\n ";

  $ret .= $urlArgs;
  if ( ! empty ( $user ) && $user != $login ) {
    $ret .= "<input type=\"hidden\" name=\"user\" value=\"$user\" />\n";
  }
  if ( ! empty ( $cat_id ) && $CATEGORIES_ENABLED == 'Y'
    && ( ! $user || $user == $login ) ) {
    $ret .= "<input type=\"hidden\" name=\"cat_id\" value=\"$cat_id\" />\n";
  }

  $ret .= '<label for="monthselect"><a href="javascript:document.SelectMonth.submit()">' .    translate( 'Month' ) . "</a>:&nbsp;</label>\n";
  $ret .= '<select name="date" id="monthselect" ' .
    'onchange="document.SelectMonth.submit()">' . "\n";

  if ( ! empty ( $thisyear ) && ! empty ( $thismonth ) ) {
    $m = $thismonth;
    $y = $thisyear;
  } else {
    $m = date ( 'm' );
    $y = date ( 'Y' );
  }
  $d_time = mktime ( 0, 0, 0, $m, 1, $y );
  $thisdate = date ( 'Ymd', $d_time );
  //$y--;
  $m -= 7;
  for ( $i = 0; $i < 25; $i++ ) {
    $m++;
    if ( $m > 12 ) {
      $m = 1;
      $y++;
    }
    if ( $y >= 1970 && $y < 2038 ) {
      $d = mktime ( 0, 0, 0, $m, 1, $y );
      $dateYmd = date ( 'Ymd', $d );
      $ret .= '<option value="' . $dateYmd . '"';
      if ( $dateYmd == $thisdate ) {
        $ret .= ' selected="selected" ';
      }
      $ret .= '>';
      $ret .= date_to_str ( $dateYmd, $DATE_FORMAT_MY, false, true, 0 );
      $ret .= "</option>\n";
    }
  }

  $ret .= "</select>\n";
  if ( $menu == false )
    $ret .= '<input type="submit" value="' . translate( 'Go' ) . "\" />\n";
  $ret .= "</form>\n";
  if ( $menu == true ) 
    $ret .= '</td><td class="ThemeMenubackgr ThemeMenu">' . "\n";

  if ( access_can_view_page ( 'week.php' ) ) {
    $weekUrl = 'week.php';
    $urlArgs = '';
  } else {
    $weekUrl = $GLOBALS['STARTVIEW'];
    if ( preg_match ( "/[?&](\S+)=(\S+)/", $weekUrl, $match ) ) {
      $weekUrl = $match[0];
      $urlArgs = "<input type=\"hidden\" name=\"$match[1]\" value=\"$match[2]\" />\n";
    }
  }
  $formclass = ( $menu == true ? 'weekmenu' : 'weekform' );
  $ret .= "<form action=\"$weekUrl\" method=\"get\" name=\"SelectWeek\" id=\"$formclass\">\n";
  $ret .= $urlArgs;
  if ( ! empty ( $user ) && $user != $login ) {
    $ret .= "<input type=\"hidden\" name=\"user\" value=\"$user\" />\n";
  }
  if ( ! empty ( $cat_id ) && $CATEGORIES_ENABLED == 'Y'
    && ( ! $user || $user == $login ) ) {
    $ret .= "<input type=\"hidden\" name=\"cat_id\" value=\"$cat_id\" />\n";
  }

  $ret .= '<label for="weekselect"><a href="javascript:document.SelectWeek.submit()">' .    translate ( 'Week' ) . "</a>:&nbsp;</label>\n";
  $ret .= '<select name="date" id="weekselect" ' .
    "onchange=\"document.SelectWeek.submit()\">\n";
  if ( ! empty ( $thisyear ) && ! empty ( $thismonth ) ) {
    $m = $thismonth;
    $y = $thisyear;
  } else {
    $m = date ( 'm' );
    $y = date ( 'Y' );
  }
  if ( ! empty ( $thisday ) ) {
    $d = $thisday;
  } else {
    $d = date ( 'd' );
  }
  $d_time = mktime ( 0, 0, 0, $m, $d, $y );
  $thisdate = date ( 'Ymd', $d_time );
  $wday = date ( 'w', $d_time );
  // $WEEK_START equals 1 or 0 
  $wkstart = mktime ( 0, 0, 0, $m, $d - ( $wday - $WEEK_START ), $y );
  $lastDay = ( $DISPLAY_WEEKENDS == 'N'? 4 : 6 );
  for ( $i = -5; $i <= 9; $i++ ) {
    $twkstart = $wkstart + ( ONE_DAY * 7 * $i );
    $twkend = $twkstart + ( ONE_DAY * $lastDay );
    $dateSYmd = date ( 'Ymd', $twkstart );
    $dateEYmd = date ( 'Ymd', $twkend );
//  echo $twkstart . " " . $twkend;
  if ( $twkstart > 0 && $twkend < 2146021200 ) { 
      $ret .= '<option value="' . $dateSYmd . '"';
      if ( $dateSYmd <= $thisdate && $dateEYmd >= $thisdate ) {
        $ret .= ' selected="selected" ';
      }
      $ret .= '>';
      if ( ! empty ( $GLOBALS['PULLDOWN_WEEKNUMBER'] ) &&
        $GLOBALS['PULLDOWN_WEEKNUMBER'] == 'Y' ) {
        $ret .=  '(' . date( 'W', $twkstart + ONE_DAY ) . ')&nbsp;&nbsp;';
      }
      $ret .= sprintf ( "%s - %s",
        date_to_str ( $dateSYmd, $DATE_FORMAT_MD, false, true, 0 ),
        date_to_str ( $dateEYmd, $DATE_FORMAT_MD, false, true, 0 ) );
      $ret .= "</option>\n";
  }
  }

  $ret .= "</select>\n";
  if ( $menu == false )
    $ret .= '<input type="submit" value="' . translate( 'Go' ) ."\" />\n";
  $ret .= "</form>\n";
  if ( $menu == true ) 
    $ret .= '</td><td class="ThemeMenubackgr ThemeMenu" align="right">' . "\n";

  if ( access_can_view_page ( 'year.php' ) ) {
    $yearUrl = 'year.php';
    $urlArgs = '';
  } else {
    $yearUrl = $GLOBALS['STARTVIEW'];
    if ( preg_match ( "/[?&](\S+)=(\S+)/", $yearUrl, $match ) ) {
      $yearUrl = $match[0];
      $urlArgs = "<input type=\"hidden\" name=\"$match[1]\" value=\"$match[2]\" />\n";
    }
  }
  $formclass = ( $menu == true ? 'yearmenu' : 'yearform' );
  $ret .= "<form action=\"$yearUrl\" method=\"get\" name=\"SelectYear\" id=\"$formclass\">\n";
  $ret .= $urlArgs;
  if ( ! empty ( $user ) && $user != $login ) {
    $ret .= "<input type=\"hidden\" name=\"user\" value=\"$user\" />\n";
  }
  if ( ! empty ( $cat_id ) && $CATEGORIES_ENABLED == 'Y'
    && ( ! $user || $user == $login ) ) {
    $ret .= "<input type=\"hidden\" name=\"cat_id\" value=\"$cat_id\" />\n";
  }

  $ret .= "<label for=\"yearselect\"><a href=\"javascript:document.SelectYear.submit()\">" .    translate( 'Year' ) . "</a>:&nbsp;</label>\n";
  $ret .= '<select name="year" id="yearselect" '.
    "onchange=\"document.SelectYear.submit()\">\n";

  if ( ! empty ( $thisyear ) ) {
    $y = $thisyear;
  } else {
    $y = date ( 'Y' );
  }
  for ( $i = $y - 2; $i < $y + 6; $i++ ) {
   if ( $i >= 1970 && $i < 2038 ) {
      $ret .= "<option value=\"$i\"";
      if ( $i == $y ) {
        $ret .= ' selected="selected" ';
      }
      $ret .= ">$i</option>\n";
  }
  }

  $ret .= "</select>\n";
  if ( $menu == false )
    $ret .= '<input type="submit" value="' . translate( 'Go' ) . "\" />\n";
  $ret .= "</form>\n";

  return $ret;
}
?>
