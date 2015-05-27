<?php
/**
 * GEO Functions
 *
 */

// GEO FUNCTIONS
function geo_data( $zip = null, $debug = false ) {
	// do nothing if viewing admin pages (geo not needed)
	if ( is_admin() )
		return false;
	/*if( session_id() == '' ) {
		session_start();
	}*/

	$a = array();

	$ip = get_the_ip();

	$zip = ( isset( $_POST['PostalCode'] ) ) ? $_POST['PostalCode'] : ( isset( $_GET['zip'] ) ) ? $_GET['zip'] : $zip;

	if ( !empty( $zip ) ) :
		$a = geo_data_mysql_zip( $zip );
	elseif ( $ip ) :
		$a = geo_data_mysql_ip( $ip );
	else : 
		$a = array(
			'locId'				=>	0,
			'country'			=>	'US',
			'region'			=>	'',
			'city'				=>	'',
			'postalCode'		=>	'00000',
			'latitude'			=>	'',
			'longitude'			=>	'',
			'metroCode'			=>	'',
			'areacode'			=>	'',
			'ip'				=>	'',
			);
	endif;

	return $a;
}

function geo_data_mysql_connect() {
	//$livehost		= array( 'jacuzzi.com', 'www.jacuzzi.com', 'www.jacuzzihottubs.com', 'www.jacuzzi.ca/hot-tubs', 'beta.jacuzzihottubs.com', 'www.jacuzzi.ca', 'beta.jacuzzi.com', 'beta.jacuzzi.ca' );
	$devhost		= array( 'jht.ninthlink.me', 'www.nlkdevj.com' );
	$localhost		= array( 'localhost', 'local.jht', 'localhost/jacuzzi.com', 'localhost.jacuzzi.com', 'localhost.jacuzzi.ca', 'local.jacuzzi' );
	
	//if ( in_array( $_SERVER['SERVER_NAME'], $livehost ) ) {
	// set up "live" credentials by default
	$the_user = "jacuzzi_geoip";
	$the_pass = "g9WpMRjuPf";
	$the_name = "jacuzzi_geoip";
	//}
	if ( in_array( $_SERVER['SERVER_NAME'], $devhost ) ) {
		$the_user = "admin_geoip";
		$the_pass = "r4e3w2q1";
		$the_name = "admin_geoip";
	}
	if ( in_array( $_SERVER['SERVER_NAME'], $localhost ) ) {
		$the_user = "root";
		$the_pass = "";
		$the_name = "nlk_geoip";
	}
	$mysqli = new mysqli(DB_HOST, $the_user, $the_pass, $the_name);
	
	return $mysqli;
}

function geo_data_mysql_ip( $ip ) {
	$a = false;

	$mysqli = geo_data_mysql_connect();

	// Unable to MySQL? Return false
	if ($mysqli->connect_errno) {
		$error = "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
		return false;
	}
	$query = "SELECT gl.* FROM geoip_locations gl LEFT JOIN geoip_blocks gb ON gb.locId = gl.locId WHERE gb.startIpNum <= INET_ATON( ? ) AND gb.endIpNum >= INET_ATON( ? ) LIMIT 1";
	if ( $stmt = $mysqli->prepare( $query ) ) {
		$stmt->bind_param( "ss", $ip, $ip );
		$stmt->execute();
		$stmt->bind_result( $locId, $country, $region, $city, $postalCode, $latitude, $longitude, $metroCode, $areaCode );
		while ( $stmt->fetch() ) {
			$a = array(
				'locId'			=>	$locId,
				'country'		=>	$country,
				'region'		=>	$region,
				'city'			=>	$city,
				'postalCode'	=>	$postalCode,
				'latitude'		=>	$latitude,
				'longitude'		=>	$longitude,
				'metroCode'		=>	$metroCode,
				'areacode'		=>	$areaCode,
				'ip'			=>	$ip,
				);
		}
		$stmt->close();
	}
	$mysqli->close();
	return $a;
}

function geo_data_mysql_zip( $zip ) {
	$a = false;
	$mysqli = geo_data_mysql_connect();
	
	// Unable to MySQL? Return false
	if ($mysqli->connect_errno) {
		$error = "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
		return false;
	}
	$clean_zip = clean_zip( $zip );
	$query = "SELECT * FROM geoip_locations WHERE postalCode = ? LIMIT 1";
	if ( $stmt = $mysqli->prepare( $query ) ) {
		$stmt->bind_param( "s", $clean_zip );
		$stmt->execute();
		$stmt->bind_result( $locId, $country, $region, $city, $postalCode, $latitude, $longitude, $metroCode, $areaCode );
		while ( $stmt->fetch() ) {
			$a = array(
				'locId'			=>	$locId,
				'country'		=>	$country,
				'region'		=>	$region,
				'city'			=>	$city,
				'postalCode'	=>	$zip,
				'latitude'		=>	$latitude,
				'longitude'		=>	$longitude,
				'metroCode'		=>	$metroCode,
				'areacode'		=>	$areaCode,
				'ip'			=>	get_the_ip(),
				);
		}
		$stmt->close();
	}
	$mysqli->close();
	return $a;
}


// If geo_ip returns zip code, and zip code is in test_market array, return true
function msrp_display() {
	$test_market = array('48001', '48002', '48003', '48004', '48005', '48007', '48009', '48012', '48014', '48015', '48017', '48021', '48023', '48025', '48026', '48027', '48028', '48030', '48033', '48034', '48035', '48036', '48037', '48038', '48039', '48041', '48042', '48043', '48044', '48045', '48046', '48047', '48048', '48050', '48051', '48054', '48062', '48063', '48064', '48065', '48066', '48067', '48068', '48069', '48070', '48071', '48072', '48073', '48075', '48076', '48079', '48080', '48081', '48082', '48083', '48084', '48085', '48086', '48088', '48089', '48090', '48091', '48092', '48093', '48094', '48095', '48096', '48098', '48099', '48101', '48103', '48104', '48105', '48106', '48107', '48108', '48109', '48110', '48111', '48112', '48113', '48114', '48115', '48116', '48117', '48118', '48120', '48121', '48122', '48123', '48124', '48125', '48126', '48127', '48128', '48130', '48131', '48134', '48135', '48136', '48137', '48138', '48139', '48140', '48141', '48143', '48146', '48150', '48151', '48152', '48153', '48154', '48158', '48159', '48160', '48161', '48162', '48164', '48165', '48166', '48167', '48168', '48169', '48170', '48173', '48174', '48175', '48176', '48178', '48179', '48180', '48183', '48184', '48185', '48186', '48187', '48188', '48189', '48190', '48191', '48192', '48193', '48195', '48197', '48198', '48201', '48202', '48203', '48204', '48205', '48206', '48207', '48208', '48209', '48210', '48211', '48212', '48213', '48214', '48215', '48216', '48217', '48218', '48219', '48220', '48221', '48222', '48223', '48224', '48225', '48226', '48227', '48228', '48229', '48230', '48231', '48232', '48233', '48234', '48235', '48236', '48237', '48238', '48239', '48240', '48242', '48243', '48244', '48255', '48260', '48264', '48265', '48266', '48267', '48268', '48269', '48272', '48275', '48277', '48278', '48279', '48288', '48301', '48302', '48303', '48304', '48306', '48307', '48308', '48309', '48310', '48311', '48312', '48313', '48314', '48315', '48316', '48317', '48318', '48320', '48321', '48322', '48323', '48324', '48325', '48326', '48327', '48328', '48329', '48330', '48331', '48332', '48333', '48334', '48335', '48336', '48340', '48341', '48342', '48343', '48346', '48347', '48348', '48350', '48353', '48356', '48357', '48359', '48360', '48361', '48362', '48363', '48366', '48367', '48370', '48371', '48374', '48375', '48376', '48377', '48380', '48381', '48382', '48383', '48386', '48387', '48390', '48391', '48393', '48397', '48428', '48430', '48442', '48451', '48455', '48462', '48843', '48844', '48855', '49229', '49230', '49236', '49238', '49240', '49259', '49261', '49263', '49265', '49268', '49270', '49285', '49286', '49287', '93901', '93902', '93905', '93906', '93907', '93912', '93915', '93921', '93922', '93933', '93940', '93942', '93943', '93944', '93950', '93953', '93955', '93962', '94002', '94005', '94010', '94011', '94014', '94015', '94016', '94017', '94018', '94019', '94020', '94021', '94022', '94023', '94024', '94025', '94026', '94027', '94028', '94030', '94035', '94037', '94038', '94039', '94040', '94041', '94042', '94043', '94044', '94060', '94061', '94062', '94063', '94064', '94065', '94066', '94070', '94074', '94080', '94083', '94085', '94086', '94087', '94088', '94089', '94102', '94103', '94104', '94105', '94107', '94108', '94109', '94110', '94111', '94112', '94114', '94115', '94116', '94117', '94118', '94119', '94120', '94121', '94122', '94123', '94124', '94125', '94126', '94127', '94128', '94129', '94130', '94131', '94132', '94133', '94134', '94137', '94139', '94140', '94141', '94142', '94143', '94144', '94145', '94146', '94147', '94151', '94158', '94159', '94160', '94161', '94163', '94164', '94172', '94177', '94188', '94301', '94302', '94303', '94304', '94305', '94306', '94309', '94401', '94402', '94403', '94404', '94497', '94501', '94502', '94503', '94505', '94506', '94507', '94509', '94510', '94511', '94512', '94513', '94514', '94516', '94517', '94518', '94519', '94520', '94521', '94522', '94523', '94524', '94525', '94526', '94527', '94528', '94529', '94530', '94531', '94533', '94534', '94535', '94536', '94537', '94538', '94539', '94540', '94541', '94542', '94543', '94544', '94545', '94546', '94547', '94548', '94549', '94550', '94551', '94552', '94553', '94555', '94556', '94557', '94559', '94560', '94561', '94563', '94564', '94565', '94566', '94568', '94569', '94570', '94571', '94572', '94575', '94577', '94578', '94579', '94580', '94581', '94582', '94583', '94585', '94586', '94587', '94588', '94589', '94590', '94591', '94592', '94595', '94596', '94597', '94598', '94601', '94602', '94603', '94604', '94605', '94606', '94607', '94608', '94609', '94610', '94611', '94612', '94613', '94614', '94615', '94617', '94618', '94619', '94620', '94621', '94622', '94623', '94624', '94649', '94659', '94660', '94661', '94662', '94666', '94701', '94702', '94703', '94704', '94705', '94706', '94707', '94708', '94709', '94710', '94712', '94720', '94801', '94802', '94803', '94804', '94805', '94806', '94807', '94808', '94820', '94850', '94901', '94903', '94904', '94912', '94913', '94914', '94915', '94920', '94924', '94925', '94930', '94933', '94938', '94939', '94941', '94942', '94945', '94946', '94947', '94948', '94949', '94950', '94953', '94954', '94955', '94956', '94957', '94960', '94963', '94964', '94965', '94966', '94970', '94973', '94974', '94975', '94976', '94977', '94978', '94979', '94998', '94999', '95001', '95002', '95003', '95004', '95005', '95006', '95007', '95008', '95009', '95010', '95011', '95012', '95013', '95014', '95015', '95017', '95018', '95019', '95020', '95021', '95026', '95030', '95031', '95032', '95033', '95035', '95036', '95037', '95038', '95039', '95041', '95042', '95044', '95045', '95046', '95050', '95051', '95052', '95053', '95054', '95055', '95056', '95060', '95061', '95062', '95063', '95064', '95065', '95066', '95067', '95070', '95071', '95073', '95076', '95077', '95101', '95103', '95106', '95108', '95109', '95110', '95111', '95112', '95113', '95115', '95116', '95117', '95118', '95119', '95120', '95121', '95122', '95123', '95124', '95125', '95126', '95127', '95128', '95129', '95130', '95131', '95132', '95133', '95134', '95135', '95136', '95138', '95139', '95140', '95141', '95148', '95150', '95151', '95152', '95153', '95154', '95155', '95156', '95157', '95158', '95159', '95160', '95161', '95164', '95170', '95172', '95173', '95190', '95191', '95192', '95193', '95194', '95196', '95416', '95433', '95476', '95487', '95620', '95625', '95641', '95680', '95687', '95688', '95690', '95696');
	$a = geo_data();
	if ( in_array( $a['postalCode'], $test_market ) ) {
		return true;
	}
}

add_action('wp_head', 'geo_meta_debug');
function geo_meta_debug() {
	$o = geo_data();
	echo '<meta name="geo_debug" content="'.implode(', ',$o).'">';
}



?>