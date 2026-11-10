<?php
/**
 * Account Balance Warning (Food Service) Staff Widget class
 *
 * @since 12.7
 *
 * @package RosarioSIS
 */

namespace RosarioSIS\StaffWidget;

class FsaBalanceWarning extends FsaBalance
{
	function html( $value = '' )
	{
		$value = $GLOBALS['warning'];

		return parent::html( $value );
	}
}
