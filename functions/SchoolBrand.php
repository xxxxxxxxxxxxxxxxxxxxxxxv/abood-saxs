<?php
/**
 * School identity helpers.
 *
 * @package RosarioSIS
 * @subpackage functions
 */

function SchoolBrandName()
{
	global $SchoolName;

	return isset( $SchoolName ) && $SchoolName !== '' ?
		$SchoolName : 'مدرسة الشهيد حازم مكاوي';
}

function SchoolBrandNameEnglish()
{
	global $SchoolNameEnglish;

	return isset( $SchoolNameEnglish ) && $SchoolNameEnglish !== '' ?
		$SchoolNameEnglish : 'Martyr Hazem Makawy School';
}

function SchoolBrandLogo()
{
	global $SchoolBrandLogo;

	return isset( $SchoolBrandLogo ) && $SchoolBrandLogo !== '' ?
		$SchoolBrandLogo : 'assets/school-brand/logo.png';
}

function SchoolBrandIcon()
{
	global $SchoolBrandIcon;

	return isset( $SchoolBrandIcon ) && $SchoolBrandIcon !== '' ?
		$SchoolBrandIcon : 'assets/school-brand/icon.png';
}

function SchoolBrandTouchIcon()
{
	global $SchoolBrandTouchIcon;

	return isset( $SchoolBrandTouchIcon ) && $SchoolBrandTouchIcon !== '' ?
		$SchoolBrandTouchIcon : 'assets/school-brand/apple-touch-icon.png';
}

function SchoolBrandSchoolImage()
{
	global $SchoolBrandSchoolImage;

	return isset( $SchoolBrandSchoolImage ) && $SchoolBrandSchoolImage !== '' ?
		$SchoolBrandSchoolImage : 'assets/school-brand/school-photo.jpg';
}

function SchoolBrandLogoForSchool( $school_id )
{
	$school_logo = 'assets/school_logo_' . (int) $school_id . '.jpg';

	if ( is_file( $school_logo ) )
	{
		return $school_logo;
	}

	$brand_logo = SchoolBrandLogo();

	return is_file( $brand_logo ) ? $brand_logo : '';
}
