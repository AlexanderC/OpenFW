<?php defined('__PROFILER_ENABLED') or die("Access Denied");
/**
 * @author AlexanderC <self@alexanderc.me>
 * @date 11/12/13
 * @time 2:15 PM
 */

// enable profiling if possible on localhost
if(function_exists('xhprof_enable')) {
    // define some needed vars
    $profilerRunsFile = realpath(__DIR__ . '/../app/cache/') . '/xhprof/runs.php';
    $appNamespace = 'OpenFW.xhprof';

    // symfony info
    $target = realpath(__DIR__ . "/../includes/xhprof/xhprof_html/");
    $link = __DIR__ . "/xhprof_html";
    $command = sprintf("ln -s %s %s", escapeshellarg($target), escapeshellarg($link));

    // carate symlink to xhprof resources is does not exists
    if(!is_link($link)) {
        passthru($command);
    }

    if(isset($_REQUEST['xhp'])) {
        echo "<h1>The list of XHprof runs.</h1>";

        // get runs cache file
        $runs = @include($profilerRunsFile);
        if(!is_array($runs)) {
            $runs = [];
        }

        // dump all known runs
        echo "<ol>";
        foreach(array_reverse($runs) as $run) {
            echo "<li><a href='/xhprof_html/index.php?run={$run['id']}&source={$appNamespace}' >",
            "{$run['id']} :: <b>",
            date('d M Y [H:i:s]', $run['time']),
            "</b></a></li>";
        }
        echo "</ol>";
        exit;
    } else {
        // enable profiling
        xhprof_enable(XHPROF_FLAGS_NO_BUILTINS | XHPROF_FLAGS_CPU | XHPROF_FLAGS_MEMORY);

        // dump result on script shutdown
        register_shutdown_function(function() use ($profilerRunsFile, $appNamespace) {
                $XHprofData = xhprof_disable();

                require_once __DIR__ . "/../includes/xhprof/xhprof_lib/utils/xhprof_lib.php";
                require_once __DIR__ . "/../includes/xhprof/xhprof_lib/utils/xhprof_runs.php";

                $XHprofRuns = new XHProfRuns_Default();
                $runId = $XHprofRuns->save_run($XHprofData, $appNamespace);

                $runs = @include($profilerRunsFile);
                if(!is_array($runs)) {
                    $runs = [];
                }

                $runs[] = ['id' => $runId, 'time' => time()];

                $cacheDir = dirname($profilerRunsFile);

                // create cache file directory
                if(!is_dir($cacheDir)) {
                    mkdir($cacheDir, 0777, true);
                }

                file_put_contents(
                    $profilerRunsFile, sprintf("<?php return %s;", var_export($runs, true)), LOCK_EX | LOCK_NB
                );
            });
    }
}