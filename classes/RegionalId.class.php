<?php
/**
 * PHP Class for formatting, generating, and verifying regional identification numbers.
 *
 * Currently supported numbers:
 * ============================
 *
 * Regional Id:
 * ------------
 * - Canadian Health Number - Ontario(OHIP)
 * - Canadian Provincial Driver's Licence Numbers - Ontario, Quebec, Alberta, British Columbia, Nova Scotia, P.E.I.,
 * 
 * The following information comes from http://www.hackcanada.com/canadian/other/sin.html
 *
 * The SIN algorithm is commonly known as the LUHN algorithm or the mod-10 algorithm. 
 * It also happens to be used to validate Credit Card numbers among other things.
 * 
 * The Canadian government uses the same authentication algorithm on many, if not all, of its "unique" numbers. 
 * It is used for employer account numbers, trust numbers, Income Tax Filer identification (your H&R Block e-file rep), 
 * and the first nine digits of the Business Number (BN). 
 * 
 * When there are letters in the identification number the following table is used to convert the letters to numbers:
 * 
 * A    B    C    D    E    F    G    H    I
 * J    K    L    M    N    O    P    Q    R
 *      S    T    U    V    W    X    Y    Z
 * -----------------------------------------
 * 1    2    3    4    5    6    7    8    9
 *
 */ 
class RegionalId
{	
	const INVALID_REGION		= 98;
	const INVALID_COUNTRY	= 99;

	const CA_HEALTH_ON_VALID_LENGTH						=  10;
	const CA_HEALTH_ON_INVALID_LENGTH					=  99;
	const CA_HEALTH_ON_MIN_LENGTH_VERSION_CODE		=   0;
	const CA_HEALTH_ON_MAX_LENGTH_VERSION_CODE		=   2;
	const CA_HEALTH_ON_INVALID_LENGTH_VERISON_CODE	= 101;
	const CA_HEALTH_ON_INVALID_VERSION_CODE			= 103;
	
	const CA_DRIVER_ON_VALID_LENGTH	=  15;
	const CA_DRIVER_PQ_VALID_LENGTH	=  13;
	
	
	/**
	 * Format a Regional ID number.
	 *
	 * @author André Fortin <andre.v.fortin@gmail.com>
	 */
	 public function format( $country_code = 'CA', $type = 'DRIVER', $region_code = 'ON', $id_number = '000-000-000-000-000' )
	{
		$result = array();
		switch ( strtoupper( $country_code ) )
		{
			case 'CA':
				switch( strtoupper( $type ) )
				{
					case 'HEALTH':
						$result = $this->format_CA_HEALTH( $id_number, $region_code );
						break;
					case 'DRIVER':
						$result = $this->format_CA_DRIVER( $id_number, $region_code );
					default:
				}
				break;
				
			case 'US':
				switch( $type )
				{
					default:
				}
				break;
			default:
		}
		
		return $result;
	}
	
	
	/**
	 * Generate a Regional ID number.
	 *
	 * @author André Fortin <andre.v.fortin@gmail.com>
	 */
	public function generate( $country_code = 'CA', $type = 'SIN', $region_code = '', $optional = null )
	{
		//echo "<br/>$country_code<br/>$type<br/>$region_code<br/>$optional<br/>";
		
		$result = array();
		switch ( strtoupper( $country_code ) )
		{
			case 'CA':
				switch( strtoupper( $type ) )
				{
					case 'DRIVER':
						$result = $this->generate_CA_DRIVER( $region_code, $optional );
						break;
					case 'HEALTH':
						$result = $this->generate_CA_HEALTH( $region_code, $optional );
						break;
					default:
				}
				break;
				
			case 'US':
				switch( $type )
				{
					default:
				}
				break;
			default:
		}
		
		return $result;
	}
	
	
	/**
	 * Validate a Regional ID number.
	 *
	 * @author André Fortin <andre.v.fortin@gmail.com>
	 */
	public function validate( $country_code = 'CA', $type = 'DRIVER', $region_code = '', $id_number = '000-000-000' )
	{
		$result = array();
		switch ( strtoupper( $country_code ) )
		{
			case 'CA':
				switch( strtoupper( $type ) )
				{
					case 'DRIVER':
						$result = $this->validate_CA_DRIVER( $id_number, $region_code = 'ON' );
						break;
					case 'HEALTH':
						$result = $this->validate_CA_HEALTH( $id_number, $region_code = 'ON' );
						break;
					default:
				}
				break;
				
			case 'US':
				switch( $type )
				{
					default:
				}
				break;
			default:
		}
		
		return $result;
	}
	 
	 
	/**
	 * Calculate the check-digit for any number based on the LUHN algorithm.
	 *
	 * @author André Fortin <andre.v.fortin@gmail.com>
	 *
	 * @see http://en.wikipedia.org/wiki/Luhn_algorithm
	 */
	private function luhn_checksum( $id_number, $mod = 10 )
	{
		// Remove any non-numeric characters.
		//$id_number = preg_replace("/[^0-9]/", "", (string)$id_number );
		
		$rev_id_number = strrev( (string) $id_number );
		$id_number_checksum = 0;
		
		foreach ( str_split( $rev_id_number ) as $index => $digit )
		{ 
			if( $index % 2 !== 0 )
			{
				$id_number_checksum += $digit * 2;
			} else {
				$id_number_checksum += $digit;
			}
		}
		
		return ( $id_number_checksum % $mod );
	}
	
	
	/**
	 * Validate a number based on the LUHN algorithm.
	 *
	 * @author André Fortin <andre.v.fortin@gmail.com>
	 *
	 */
	private function is_valid_luhn( $id_number, $mod = 10 )
	{
		// Remove any non-numeric characters.
		//$id_number = preg_replace("/[^0-9]/", "", (string)$id_number );
		
		$partial_number = substr( $id_number, 0, strlen( $id_number ) - 1 );
		$check_digit = substr( $id_number, - 1 );

		$checksum = $this->luhn_checksum( $partial_number, $mod );
		
		$result = NULL;
		if( $check_digit == $checksum )
		{
			$result = true;
		} else {
			$result = false;
		}
		
		return $result;
	}
	
	
	/**
	 * Format a Canadian Health Number.
	 *
	 * @author André Fortin <andre.v.fortin@gmail.com>
	 */
	private function format_CA_HEALTH( $id_number, $region_code = 'ON' )
	{
		
		switch( strtoupper( $region_code ) )
		{
			case 'ON': // 10 characters.
				// convert to a string and strip any non-numeric characters.
				$id_number = (string)$id_number;
				$id_number = preg_replace("/[^0-9,A-Z]/", "", $id_number );
				
				$formatted_number  = substr( $id_number, 0, 4 );
				$formatted_number .= '-';
				$formatted_number .= substr( $id_number, 4, 3 );
				$formatted_number .= '-';
				$formatted_number .= substr( $id_number, 7, 3 );
				
				if( strlen( $id_number ) > self::CA_HEALTH_ON_VALID_LENGTH )
				{
					$formatted_number .= '-';
					$formatted_number .= substr( $id_number, 10, 2 );
				}
				break;
				
			case 'PQ': 
			case 'NS':
			case 'PE':
			case 'AB':
			case 'BC':
			case 'NB':
			case 'NF':
			case 'MB':
			case 'SK':
			case 'NT':
			case 'NU':
			case 'YU':
			default:
				$formatted_number = $id_number;
		}
				
		return $formatted_number;
	}
	
	
	/**
	 * Generate a valid looking Canadian Health Number.
	 *
	 * @author André Fortin <andre.v.fortin@gmail.com>
	 *
	 */
	private function generate_CA_HEALTH( $region_code = 'ON', $optional = null )
	{
		$id_number = '';
		
		switch( strtoupper( $region_code ) )
		{
			case 'ON': // Generate the Ontario Health Number, formerly OHIP number.
				for( $digits = 0; $digits < self::CA_HEALTH_ON_VALID_LENGTH; $digits++ )
				{
					$id_number .= rand( 0, 9 );
				}
				
				// Now generate the Version Code.
				for( $letters = 0; $letters < self::CA_HEALTH_ON_MAX_LENGTH_VERSION_CODE; $letters++ )
				{
					$int = rand( 0, 23 );
					$vc_letters = "ABCDEFGHJKLMNPQRSTUVWXYZ";
					$rand_letter = $vc_letters[$int];
					
					$id_number .= $rand_letter;
				}
				break;
				
			case 'PQ': 
			case 'NS':
			case 'PE':
			case 'AB':
			case 'BC':
			case 'NB':
			case 'NF':
			case 'MB':
			case 'SK':
			case 'NT':
			case 'NU':
			case 'YU':
			default:
				$id_number = '';
		}
		
		return $id_number;
	}
	
	
	/**
	 * Validate a Canadian Health Number.
	 *
	 * @author André Fortin <andre.v.fortin@gmail.com>
	 */
	private function validate_CA_HEALTH( $id_number, $region_code = 'ON' )
	{
		$is_valid	= false;
		$error_code	= 0;
		
		switch( strtoupper( $region_code ) )
		{
			case 'ON': // Validate the Ontario Health Number, formerly OHIP number.
				// Convert to a string and strip any non-numeric characters.
				$id_number = strtoupper( (string)$id_number );
				$id_number = preg_replace("/[^0-9,A-Z]/", "", $id_number );
				
				// Validate the Health Number length.
				$health_number = substr( $id_number, 0, self::CA_HEALTH_ON_VALID_LENGTH );
				if( self::CA_HEALTH_ON_VALID_LENGTH == strlen( $health_number ) )
				{
					// Validate the version code if present.
					$version_code = substr( $id_number, 10, 2 );
					if( strlen( $version_code ) >= self::CA_HEALTH_ON_MIN_LENGTH_VERSION_CODE && 
						strlen( $version_code ) <= self::CA_HEALTH_ON_MAX_LENGTH_VERSION_CODE )
					{
						$result = strpos( $version_code, 'I' );
						if( false === $result )
						{
							$result = strpos( $version_code, 'O' );
							if( false === $result )
							{
								$is_valid = true;
							} else {
								$is_valid = false;
								$error_code = self::CA_HEALTH_ON_INVALID_VERSION_CODE;
							}
						} else {
							$is_valid = false;
							$error_code = self::CA_HEALTH_ON_INVALID_VERSION_CODE;
						}
					} else {
						$is_valid = false;
						$error_code = self::CA_HEALTH_ON_INVALID_LENGTH_VERISON_CODE;
					}

				} else {
					$is_valid = false;
					$error_code = self::CA_HEALTH_ON_INVALID_LENGTH;
				}
				break;
				
			case 'PQ': 
			case 'NS':
			case 'PE':
			case 'AB':
			case 'BC':
			case 'NB':
			case 'NF':
			case 'MB':
			case 'SK':
			case 'NT':
			case 'NU':
			case 'YU':
			default:
				$is_valid	= false;
				$error_code	= INVALID_REGION;
				$id_number	= '';
		}
		
		return array( "success"=>$is_valid, "code"=>$error_code, "id_number"=>$id_number );
	}	
	
	
	/**
	 * Format a Canadian Driver's Licence Number.
	 *
	 * @author André Fortin <andre.v.fortin@gmail.com>
	 */
	private function format_CA_DRIVER( $id_number, $region_code = 'ON' )
	{
		// Initialize local variables.
		$formatted_number = '';
		
		// convert to a string and strip any non-numeric characters.
		$id_number = (string)$id_number;
		$id_number = preg_replace("/[^0-9,A-Z,a-z,-]/", "", $id_number );
		$id_number = strtoupper( $id_number );
		$id_number .= '               '; // Add 15 ' 's to the number to make sure its long enough.
		
		switch( strtoupper( $region_code ) )
		{
			case 'ON': // 15 characters.
				// Add the formatting.
				$id_number = preg_replace("/[-]/", "", $id_number );
				$formatted_number  = substr( $id_number, 0,  5 ); // Groups of 5 characters.
				$formatted_number .= '-';
				$formatted_number .= substr( $id_number, 5,  5 ); // Group of 5 characters.
				$formatted_number .= '-';
				$formatted_number .= substr( $id_number, 10, 5 ); // Group of 5 characters.
				break;
				
			case 'PQ': // 14 characters
				// Add the formatting.
				$id_number = preg_replace("/[-]/", "", $id_number );
				$formatted_number  = substr( $id_number, 0,  5 ); // Groups of 5 characters.
				$formatted_number .= '-';
				$formatted_number .= substr( $id_number, 5,  6 ); // Group of 6 characters.
				$formatted_number .= '-';
				$formatted_number .= substr( $id_number, 11, 2 ); // Group of 2 characters.
				break;
			
			case 'NS': // 14 characters
				$formatted_number  = substr( $id_number, 0,  14 ); // Group of 14 characters.
				break;
				
			case 'PE':
				// 6 characters
				$id_number = preg_replace("/[A-Z,a-z,-]/", "", $id_number );
				$formatted_number = substr( $id_number, 0, 6 ); // Group of 6 characters.
				break;
				
			case 'AB':
				// 9 characters
				$id_number = preg_replace("/[A-Z,a-z,-]/", "", $id_number );
				$formatted_number = substr( $id_number, 0, 9 ); // Group of 9 characters.
				break;
			
			case 'BC':
				// 9 characters
				$id_number = preg_replace("/[A-Z,a-z,-]/", "", $id_number );
				$formatted_number = substr( $id_number, 0, 9 ); // Group of 9 characters.
				break;	
				
			case 'NB':
			case 'NF':
			case 'MB':
			case 'SK':
			case 'NT':
			case 'NU':
			case 'YU':
			default:
				$formatted_number = $id_number;
		}
		
		return trim( $formatted_number );
	}
	
	
	/**
	 * Generate a valid looking Canadian Driver's Licence Number.
	 *
	 * optional array data example.
	 * array({'last_name'=>'FORTIN', 'day_of_birth'=>'2014-03-18', 'gender'=>'M'})
	 *
	 * @author André Fortin <andre.v.fortin@gmail.com>
	 *
	 */
	private function generate_CA_DRIVER( $region_code = 'ON', $optional = null )
	{
		// Initialize local variables.
		$id_number = '';
		$random_letters = '';
		$random_numbers = '';
		
		// Create random letter string of 15 characters.
		$letters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$key_size = strlen( $letters );
		for( $count = 0; $count < 15; $count++ ) {
			$random_letters .= $letters[ rand( 0, $key_size - 1 ) ];
		}
		// Create random number string of 15 characters.
		for( $count = 0; $count < 15; $count++ ) {
			$random_numbers .= (string)rand( 0, 9 );
		}
		
		// Create empty values if we are missing any.
		if( !is_array( $optional ) ) {
			$optional = array( 'last_name'=>'', 'day_of_birth'=>'', 'gender'=>'' );
		} else {
			if( !array_key_exists( 'last_name', $optional ) ) {
				$optional['last_name'] = '';
			} elseif( !array_key_exists( 'day_of_birth', $optional ) ) {
				$optional['day_of_birth'] = '';
			} elseif( !array_key_exists( 'gender', $optional ) ) {
				$optional['gender'] = '';
			}
		}
		
		// --------------------------------------------------------
		// Validate or generate the optional data.
		// --------------------------------------------------------
		
		// Some Provinces encode part of the last name.
		$optional['last_name'] = (string)$optional['last_name'];
		$optional['last_name'] = preg_replace("/[^A-Z,a-z]/", "", $optional['last_name'] );
		$optional['last_name'] = strtoupper( $optional['last_name'] );
		
		if( strlen( $optional['last_name'] ) < 5 )
		{
			// Generate a random string of 5 characters with '-' as spaces.
			$random_data = substr( $random_letters, 0, 1 );
			$letters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ-';
			$key_size = strlen( $letters );
			for( $count = 0; $count < 4; $count++ ) {
				$letter = $letters[ rand( 0, $key_size - 1 ) ];
				if( '-' == $letter ) {
					$random_data .= '------';
					$count = 5;
				} else {
					$random_data .= $letter;
				}
			}

			$optional['last_name'] = substr( $random_data, 0, 5 );
		}
				
		// Some Provinces encode the gender.
		$optional['gender'] = strtoupper( $optional['gender'] );
		if( !(( 'M' === $optional['gender'] ) || ('F' === $optional['gender'] )) ) {
			// Generate random gender.
			$letters = 'MF';
			$key_size = strlen( $letters );
			for( $count = 0; $count < 1; $count++ ) {
				$optional['gender'] = $letters[ rand( 0, $key_size - 1 ) ];
			}
		}
		
		// Some Provinces encode the day of birth.
		$optional['day_of_birth'] = preg_replace("/[^0-9,A-Z,-]/", "", $optional['day_of_birth'] );
		if( strlen( $optional['day_of_birth'] ) < 10 ) {
			// Generate random birth of between 19 and 65 years of age.
			$year		= (string)((int)date('Y') - rand( 19, 65 ));
			$month	= rand( 1, 12 );
			$day		= 0;
			switch( $month )
			{
				case  2: // February = 28 days.
					$day = rand( 1, 28 );
					break;
				case  4: // Sept, April, June, Nov = 30 days.
				case  6:
				case  9:
				case 11: 
					$day = rand( 1, 30 );
					break;
				default: // All the rest = 31 days.
					$day = rand( 1, 31 );
			}
			$optional['day_of_birth'] = (string)$year.'-'.
				substr( '0'.(string)$month, -2 ).'-'.
				substr( '0'.(string)$day, -2 );
		}
		
		switch ( strtoupper( $region_code ))
		{
			case 'ON':
				/**
				 * 15 characters
				 * First digit of last name
				 * 8 random digits
				 * 2 digit year of birth
				 * 2 digit month of birth + 50 if gender is female
				 * 2 digit day of birth
				 */
				$id_number  = strtoupper( substr( $optional['last_name'], 0, 1 ) );
				$id_number .= substr( $random_numbers, 0, 8 );
				$id_number .= substr( $optional['day_of_birth'], 2, 2 );
				if( 'M' === $optional['gender'] ) {
					$id_number .= substr( $optional['day_of_birth'], 5, 2 );
				} else {
					$id_number .= (string)((int)substr( $optional['day_of_birth'], 5, 2 ) + 50);
				}
				$id_number .= substr( $optional['day_of_birth'], 8, 2 );
				break;
				
			case 'PQ':
				/**
				 * 13 characters
				 * First letter of last name
				 * 4 random digits
				 * 2 digit day of birth
				 * 2 digit month of birth
				 * 2 digit year of birth
				 * 2 random digits
				 */
				$id_number  = strtoupper( substr( $optional['last_name'], 0, 1 ) );
				$id_number .= substr( $random_numbers, 0, 4 );
				$id_number .= substr( $optional['day_of_birth'], 8, 2 );
				$id_number .= substr( $optional['day_of_birth'], 5, 2 );
				$id_number .= substr( $optional['day_of_birth'], 2, 2 );
				$id_number .= substr( $random_numbers, 4, 2 );
				break;
			
			case 'NS':
				/**
				 * See www.novascotia.ca/snsmr/rmv/other/id_req.asp
				 * for information on the "Master Number" or "Client Master Number"
				 * 14 character number = 5 letters + 9 digits
				 * First 5 letters of last name. If last name less than 5 characters, 
				 * the difference is made up with spaces identified by the '-' character.
				 * 2 digit day of birth
				 * 2 digit month of birth
				 * 2 digit year of birth
				 * 3 computer-assigned numbers - ( my guess is overflow numbers )
				 */
				$id_number  = strtoupper( substr( $optional['last_name'], 0, 5 ) );
				$id_number .= substr( $optional['day_of_birth'], 8, 2 );
				$id_number .= substr( $optional['day_of_birth'], 5, 2 );
				$id_number .= substr( $optional['day_of_birth'], 2, 2 );
				$id_number .= substr( '00'.(string)rand( 0, 12 ), -3 );
				break;
				
			case 'PE':
				// 6 random digits
				$id_number .= substr( $random_numbers, 0, 6 );
				break;
				
			case 'AB':
				// 9 random digits
				$id_number .= substr( $random_numbers, 0, 9 );
				break;

			case 'BC':
				// 9 random digits
				$id_number .= substr( $random_numbers, 0, 7 );
				break;
				
			case 'NB':
			case 'NF':
			case 'MB':
			case 'SK':
			case 'NT':
			case 'NU':
			case 'YU':
			default:
				$id_number = substr( $random_letters, 0, 1 ).substr(  $random_numbers,0, 14 );
		}
		
		return $id_number;
	}	
	

}

/*
$oRegionalId = new RegionalId();

$result = $oRegionalId->generate( 'CA', 'HEALTH', 'ON' );
$result = $oRegionalId->validate( 'CA', 'HEALTH', 'ON', $result );
echo "HEALTH = [" . $oRegionalId->format( 'CA', 'HEALTH', 'ON', $result["id_number"] ) . "]<br/>";
var_dump( $result );

*/
