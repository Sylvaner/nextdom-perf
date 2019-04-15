<?php
    $scriptResult = true;
    $jeedomResult = json_decode(file_get_contents('result_jeedom.json'));
    $nextDomResult = json_decode(file_get_contents('result_nextdom.json'));
    array_shift($jeedomResult);
    array_shift($nextDomResult);

    $result = [];
    $result[] = ['', 'Average', 'Best', 'Worst'];
    for ($testIndex = 0; $testIndex < count($jeedomResult); ++$testIndex) {
        $name = $jeedomResult[$testIndex][0];
        $jeedomAverage = $jeedomResult[$testIndex][1];
        $nextDomAverage = $nextDomResult[$testIndex][1];
        $bestNextDom = $nextDomResult[$testIndex][2];
        $bestJeedom = $jeedomResult[$testIndex][2];
        $worstNextDom = $nextDomResult[$testIndex][2];
        $worstJeedom = $jeedomResult[$testIndex][2];
        for ($res = 3; $res < count($jeedomResult[$testIndex]); ++$res) {
            if ($jeedomResult[$testIndex][$res] < $bestJeedom) {
                $bestJeedom = $jeedomResult[$testIndex][$res];
            }
            if ($nextDomResult[$testIndex][$res] < $bestNextDom) {
                $bestNextDom = $nextDomResult[$testIndex][$res];
            }
            if ($jeedomResult[$testIndex][$res] > $worstJeedom) {
                $worstJeedom = $jeedomResult[$testIndex][$res];
            }
            if ($nextDomResult[$testIndex][$res] > $worstNextDom) {
                $worstNextDom = $nextDomResult[$testIndex][$res];
            }
        }

        $warning = '';
        if ($bestJeedom < $bestNextDom || $worstJeedom < $worstNextDom) {
            $warning = " war";
        }
        if ($jeedomAverage < $nextDomAverage) {
            $warning = " WAR";
            $scriptResult = false;
        }
        $result[] = [$name, '', '', ''];
        $result[] = ['Jeedom', $jeedomAverage, $bestJeedom, $worstJeedom];
        $result[] = ['NextDom' . $warning, $nextDomAverage, $bestNextDom, $worstNextDom];
        $result[] = [' => ', round((1 - $jeedomAverage/$nextDomAverage) * 100, 2) . '%', round((1 - $bestJeedom/$bestNextDom) * 100, 2) . '%', round((1 - $worstJeedom/$worstNextDom) * 100, 2) . '%'];
    }

	$colsSize = array_fill(0, 4, 0);
	// Calculate column size
	foreach ($result as $row) {
		for ($i = 0; $i < count($row); ++$i) {
			if ($colsSize[$i] < strlen(strval($row[$i]))) {
				$colsSize[$i] = strlen(strval($row[$i]));
			}
		}
	}
	// Display data
    echo ' ' . str_repeat('-', array_sum($colsSize) + 5 * count($colsSize) + 1) . "\n";
	foreach ($result as $index => $row) {
		for ($i = 0; $i < count($row); ++$i) {
            if (strpos($row[$i], 'WAR') !== false) {
                $row[$i] = str_replace("WAR", "\033[0;31m/!\\\033[0m", $row[$i]);
                echo ' | ' . str_pad($row[$i], $colsSize[$i] + 13);
            } elseif (strpos($row[$i], 'war') !== false) {
                $row[$i] = str_replace("war", "\033[0;33m/!\\\033[0m", $row[$i]);
                echo ' | ' . str_pad($row[$i], $colsSize[$i] + 13);
            } else {
                echo ' | ' . str_pad($row[$i], $colsSize[$i] + 2);
            }
		}
		echo " |\n";
        if ($index % 4 == 0) {
			echo ' ' . str_repeat('-', array_sum($colsSize) + 5 * count($colsSize) + 1) . "\n";
		}
	}

        if (!$scriptResult) {
            exit(1);
        }
