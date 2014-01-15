<?php
/**
 * PHP Class for formatting, generating, and verifying national and regional identification numbers.
 *
 * Currently supported numbers:
 * ============================
 *
 * National Id:
 * ------------
 * - Canadian Social Insurance Number (SIN)
 * - United States Social Security Number (SSN)
 *
 * Regional Id:
 * ------------
 * - Canadian Health Number - Ontario(OHIP)
 * - Canadian Provincial Driver's Licence Numbers - Ontario, Quebec, Alberta, British Columbia, Nova Scotia, P.E.I.,
 * 
 */
require_once( "classes/NationalId.class.php" );
require_once( "classes/RegionalId.class.php" );
 
class UniqueId
{
	private static $oNationalId = null;
	private static $oRegionalId = null;
	
	
	/**
	 * Default class constructor.
	 *
	 * @author André Fortin <andre.v.fortin@gmail.com>
	 */	
	function __construct() {
		self::$oNationalId = new NationalId();
		self::$oRegionalId = new RegionalId();
	}
	
	
	/**
	 * Default class destructor.
	 *
	 * @author André Fortin <andre.v.fortin@gmail.com>
	 */
	function __destroy() {
		unset( $this->$oNationalId );
		unset( $this->$oRegionalId );
	}
	
	
	/**
	 * Format a Unique ID number.
	 *
	 * @author André Fortin <andre.v.fortin@gmail.com>
	 */
	public function format( $scope = 'NATIONAL', $country_code = 'CA', $type = 'SIN', $region_code = null, $optional = null ) {
		
		$result = '';
		switch( strtoupper( $scope ) )
		{
			case 'NATIONAL':
				$result = self::$oNationalId->format( $country_code, $type, $region_code, $optional );
				break;
			case 'REGIONAL':
				$result = self::$oRegionalId->format( $country_code, $type, $region_code, $optional );
				break;
			default:
				$result = self::$oRegionalId->format( $country_code, $type, $region_code, $optional );
		}
		
		return $result;
	}
	
	
	/**
	 * Generate a Unique ID number.
	 *
	 * @author André Fortin <andre.v.fortin@gmail.com>
	 */
	public function generate( $scope = 'NATIONAL', $country_code = 'CA', $type = 'SIN', $region_code = null, $optional = null ) {
		
		$result = '';
		switch( strtoupper( $scope ) )
		{
			case 'NATIONAL':
				$result = self::$oNationalId->generate( $country_code, $type, $region_code, $optional );
				break;
			case 'REGIONAL':
				$result = self::$oRegionalId->generate( $country_code, $type, $region_code, $optional );
				break;
			default:
				$result = self::$oRegionalId->generate( $country_code, $type, $region_code, $optional );
		}
		
		return $result;
	}
	
	
	/**
	 * Validate a Unique ID number.
	 *
	 * @author André Fortin <andre.v.fortin@gmail.com>
	 */
	public function validate( $scope = 'NATIONAL', $country_code = 'CA', $type = 'SIN', $region_code = null, $optional = null ) {
		
		$result = '';
		switch( strtoupper( $scope ) )
		{
			case 'NATIONAL':
				$result = self::$oNationalId->validate( $country_code, $type, $region_code, $optional );
				break;
			case 'REGIONAL':
				$result = self::$oRegionalId->validate( $country_code, $type, $region_code, $optional );
				break;
			default:
				$result = self::$oRegionalId->validate( $country_code, $type, $region_code, $optional );
		}
		
		return $result;
	}
}
