<?php
	require_once('core/php/core.inc.php');

	define('ALL_TESTS_ITER', 4);
	define('TESTS_ITER', 20000);

	function test_eqlogic_all() {
		eqLogic::all();
	}

	function test_eqlogic_by_id() {
		eqLogic::byId(1);
	}

	function test_cmd_all() {
		cmd::all();
	}

	function test_cmd_by_id() {
		cmd::byId(1);
	}

	function test_jeeobject_all() {
		jeeObject::all();
	}

	function test_jeeobject_by_id() {
		jeeObject::byId(1);
	}

	function test_jeeobject_get_root_objects() {
		jeeObject::rootObject();
	}

	function test_jeeobject_build_tree() {
		jeeObject::buildTree();
	}

	function test_cache_write() {
		cache::set('jeedom_benchmark', 1);
	}

	function test_cache_read() {
		$cache = cache::byKey('jeedom_benchmark');
		$cache->getValue();
	}

	function test_database_write_delete() {
		$sql = 'DELETE FROM config
				WHERE `key`="jeedom_benchmark"
				AND plugin="core"';
		try {
			DB::Prepare($sql, array(), DB::FETCH_TYPE_ROW);
		} catch (Exception $e) {
			
		}
		$sql = 'INSERT INTO config
		SET `key`="jeedom_benchmark",plugin="core",`value`="1"';
		try {
			DB::Prepare($sql, array(), DB::FETCH_TYPE_ROW);
		} catch (Exception $e) {
			
		}
	}

	function test_database_update() {
		$sql = 'UPDATE config
				SET `value`=:value
				WHERE `key`="jeedom_benchmark"
				AND plugin="core"';
		try {
			DB::Prepare($sql, array('value' => 1), DB::FETCH_TYPE_ROW);
		} catch (Exception $e) {
			
		}
	}

	function test_database_replace() {
		config::save('jeedom_benchmark', 1);
	}

	function test_database_read() {
		config::byKey('jeedom_benchmark');
	}

	$testsList = [
		'EqLogic' => [
			'eqLogic::all' => 'test_eqlogic_all',
			'eqLogic::byId' => 'test_eqlogic_by_id'
		],
		'Cmd' => [
			'cmd::all' => 'test_cmd_all',
			'cmd::byId' => 'test_cmd_by_id'
		],
		'JeeObject' => [
			'jeeObject::all' => 'test_jeeobject_all',
			'jeeObject::byId' => 'test_jeeobject_by_id',
			'jeeObject::getRootObjects' => 'test_jeeobject_get_root_objects',
			'jeeObject::buildTree' => 'test_jeeobject_build_tree'
		],
		'Jeedom' => [
			'cacheWrite' => 'test_cache_write',
			'cacheRead' => 'test_cache_read',
			'databaseWriteDelete' => 'test_database_write_delete',
			'databaseUpdate' => 'test_database_update',
			'databaseReplace' => 'test_database_replace',
			'databaseRead' => 'test_database_read'
		]
	];
	
	// Init result array
	$results = [];
	foreach ($testsList as $testTitle => $testsData) {
		$result[$testTitle] = [];
		foreach ($testsData as $testName => $testFunc) {
			$result[$testTitle][$testName] = [];
		}
	}
	// Launch all tests
	for ($pass = 0; $pass < ALL_TESTS_ITER; ++$pass) {
		echo "*** Pass " . ($pass +1) . "/" . ALL_TESTS_ITER . " ***\n";
		foreach ($testsList as $testTitle => $testsData) {
			foreach ($testsData as $testName => $testFunc) {
				$startTime = getmicrotime();
				for ($i = 0; $i < TESTS_ITER; ++$i) {
					$testFunc();
				}
				$endTime = getmicrotime();
				$results[$testTitle][$testName][$pass] = $endTime - $startTime;
			}
		}
	}

	// Prepare results
	// Header
	$tableResults = [];
	$tableResults[0] = ['Name', 'Average'];
	for ($pass = 0; $pass < ALL_TESTS_ITER; ++$pass) {
		$tableResults[0][] = "Pass " . ($pass + 1);
	}
	// Data
	foreach ($testsList as $testTitle => $testsData) {
		foreach ($testsData as $testName => $testFunc) {
			$row = [];
			$row[] = $testName;
			$sumResults = 0;
			for ($pass = 0; $pass < ALL_TESTS_ITER; ++$pass) {
				$sumResults += $results[$testTitle][$testName][$pass];
			}
			$row[] = $sumResults / ALL_TESTS_ITER;
			for ($pass = 0; $pass < ALL_TESTS_ITER; ++$pass) {
				$row[] = $results[$testTitle][$testName][$pass];
			}
			$tableResults[] = $row;
		}
	}

	file_put_contents('/var/www/html/result.json', json_encode($tableResults));
	
	$colsSize = array_fill(0, count($tableResults[0]), 0);
	// Calculate column size
	foreach ($tableResults as $row) {
		for ($i = 0; $i < count($row); ++$i) {
			if ($colsSize[$i] < strlen(strval($row[$i]))) {
				$colsSize[$i] = strlen(strval($row[$i]));
			}
		}
	}
	// Display data
	$header = true;
	foreach ($tableResults as $row) {
		for ($i = 0; $i < count($row); ++$i) {
			echo str_pad($row[$i], $colsSize[$i] + 2);
		}
		echo "\n";
		if ($header) {
			$header = false;
			echo str_repeat('-', array_sum($colsSize) + 2 * count($colsSize)) . "\n";
		}
	}
