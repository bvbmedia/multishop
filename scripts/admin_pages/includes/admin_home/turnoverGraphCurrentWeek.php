<?php
if (!defined('TYPO3_MODE')) {
    die('Access denied.');
}
$compiledWidget['key'] = 'turnoverGraphCurrentWeek';
$compiledWidget['defaultCol'] = 1;
$compiledWidget['title'] = $this->pi_getLL('turnoverGraphCurrentWeek', 'jQPlot Sample');
$compiledWidget['content'] = '
<script type="text/javascript">
$(document).ready(function(){
    var s1 = [2, 6, 7, 10];
    var s2 = [7, 5, 3, 2];
    var ticks = [\'a\', \'b\'];
     
    plot2 = $.jqplot(\'chart1\', [s1, s2], {
        seriesDefaults: {
            renderer:$.jqplot.BarRenderer,
            pointLabels: { show: true }
        },
        axes: {
            xaxis: {
                renderer: $.jqplot.CategoryAxisRenderer,
                ticks: ticks
            }
        }
    });
 
    $(\'#chart1\').bind(\'jqplotDataHighlight\', 
        function (ev, seriesIndex, pointIndex, data) {
            $(\'#info2\').html(\'series: \'+seriesIndex+\', point: \'+pointIndex+\', data: \'+data);
        }
    );
         
    $(\'#chart1\').bind(\'jqplotDataUnhighlight\', 
        function (ev) {
            $(\'#info2\').html(\'Nothing\');
        }
    );
});
</script>
<div id="chart1"></div>';
