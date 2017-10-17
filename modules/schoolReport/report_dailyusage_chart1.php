<!DOCTYPE HTML>
<html>
<head>
<meta charset="UTF-8">
<link rel="stylesheet" type="text/css" href="http://www.jq22.com/jquery/font-awesome.4.6.0.css">
<link rel="stylesheet" type="text/css" href="css/animsition.min.css">
<link rel="stylesheet" type="text/css" href="css/drop-down.css">
<link rel="stylesheet" type="text/css" href="css/common.css">
<link rel="stylesheet" type="text/css" href="css/xsfx.css">
<link rel="stylesheet" type="text/css" href="css/jedate.css">
<link rel="stylesheet" type="text/css" href="css/jquery-labelauty.css">
<style>
	:root .fdad, :root .adsbygoogle, :root #main-content > [style="padding:10px 0 0 0 !important;"],
	:root .footer > #box[style="width:100%;height:100%;position:fixed;top:0"] {display:none !important;}
	iframe[src="js/ad/ad.html"] {display:none !important;}
</style>
<script data-require-id="echarts/chart/bar" src="http://echarts.baidu.com/build/dist/chart/bar.js" async=""></script>
<script data-require-id="echarts/chart/line" src="http://echarts.baidu.com/build/dist/chart/line.js" async=""></script>
<script data-require-id="echarts/chart/pie" src="http://echarts.baidu.com/build/dist/chart/pie.js" async=""></script>
<script data-require-id="echarts/chart/map" src="http://echarts.baidu.com/build/dist/chart/map.js" async=""></script>
<script src="http://www.jq22.com/jquery/jquery-1.10.2.js"></script>
<script src="http://libs.baidu.com/jquery/1.10.2/jquery.min.js"></script>
<script src="js/jquery-labelauty.js"></script>
<script src="js/jquery.cityselect.js"></script>
<script src="js/jquery.jedate.min.js"></script>
<script src="http://www.jq22.com/jquery/jquery-ui-1.11.0.js"></script>
<script src="http://cdn.bootcss.com/jqueryui/1.11.0/jquery-ui.min.js"></script>
<script src="js/select-widget-min.js"></script>
<script src="js/jquery.animsition.min.js"></script>
<script src="http://echarts.baidu.com/build/dist/echarts.js"></script>
<script src="js/macarons.js"></script>
<script src="js/common.js"></script>
<script src="js/sq_data.js"></script>
<script>
$(document).ready(function() {
	//外掛程式初始化
	$(":checkbox").labelauty({
		checked_label: "",
		unchecked_label: "",
	});
	$(":radio").labelauty({
		checked_label: "",
		unchecked_label: "",
	});

	//初始化切換
	$(".animsition").animsition({
		inClass: 'fade-in-right',
		outClass: 'fade-out',
		inDuration: 1500,
		outDuration: 800,
		linkElement: '.animsition-link',
		// e.g. linkElement   :   'a:not([target="_blank"]):not([href^=#])'
		loading: true,
		loadingParentElement: 'body', //animsition wrapper element
		loadingClass: 'animsition-loading',
		unSupportCss: ['animation-duration', '-webkit-animation-duration',
			'-o-animation-duration'
		],
		//"unSupportCss" option allows you to disable the "animsition" in case the css property in the array is not supported by your browser.
		//The default setting is to disable the "animsition" in a browser that does not support "animation-duration".
		overlay: false,
		overlayClass: 'animsition-overlay-slide',
		overlayParentElement: 'body'
	});
	var data = [];

	function my_data() {
		for (var i = 0; i < 15; i++) {
			data.push({
				name: '名字' + i,
				value: 　Math.round(Math.random() * (500 - 100) + 100),
				num: Math.round(Math.random() * (500 - 100) + 100)
			});
		};
	}
	my_data();
	//排名前100
	var my_data100;
	var my_data100t;
	var my_data100_json;
	var my_data100_json2;
	var my_data100_name;
	var my_data100_name2;
	var my_data100_value;
	var my_data100_num;
	var len
	var iNow_len;
	var iNowEnd;
	var my_key;
	Sort_100(data)

	function Sort_100(data, ble) {
		my_data100 = [];
		my_data100t = [];
		my_data100_json = [];
		my_data100_json2 = [];
		my_data100_name = [];
		my_data100_name2 = [];
		my_data100_value = [];
		my_data100_num = [];
		len = data.length;
		iNow_len = len >= 100 ? 100 : len;
		if (iNow_len == 100) {
			iNowEnd = 13;
		} else if (iNow_len >= 80) {
			iNowEnd = 15;
		} else if (iNow_len >= 60) {
			iNowEnd = 20;
		} else if (iNow_len >= 40) {
			iNowEnd = 30;
		} else if (iNow_len >= 20) {
			iNowEnd = 48;
		} else if (iNow_len >= 15) {
			iNowEnd = 70;
		} else {
			iNowEnd = 100;
		}
		for (var i = 0; i < len; i++) {
			my_data100[i] = $.extend(true, {}, data[i]);
			my_data100t[i] = $.extend(true, {}, data[i]);
		}
		for (var i = 0; i < (len >= 100 ? 100 : len); i++) {
			my_data100_json.unshift(size('value', my_data100)[0]);
			my_data100_name.unshift(my_data100_json[0].name);
			my_data100_num.unshift(my_data100_json[0].value);
		}
		for (var i = 0; i < (len >= 100 ? 100 : len); i++) {
			my_data100_json2.unshift(size('num', my_data100t)[0]);
			my_data100_name2.unshift(my_data100_json2[0].name);
			my_data100_value.unshift(my_data100_json2[0].num);
		}

		function size(key, obj) {
			var j = obj[0][key];
			var n = obj[0];
			var iNow = 0;
			for (var i = 1; i < obj.length; i++) {
				if (obj[i][key] > j) {
					j = obj[i][key];
					iNow = i;
				}
			}
			return obj.splice(iNow, 1);
		}
		/*console.log(my_data100_num)*/
		console.log(1233,my_data100_value)
	}
	// 路徑配置
	require.config({
		paths: {
			echarts: 'http://echarts.baidu.com/build/dist'
		}
	});

	require(
		['echarts', 'echarts/chart/bar', 'echarts/chart/line',
			'echarts/chart/pie', 'echarts/chart/map', // 使用柱狀圖就載入bar模組，按需載入
		],
		function(ec) {
			// 基於準備好的dom，初始化echarts圖表
			var myChart = ec.init(document.getElementById('main1'), 'macarons');
			var ecConfig = require('echarts/config');
			var option = {
				backgroundColor: 'white',
				title: {
					text: '學校使用狀況',
					x: 48,
					y: 15,
					textStyle: {
						fontSize: 12
					}
				},
				tooltip: {
					trigger: 'axis',
				},
				toolbox: {
					show: false,
					y: 15,
					x: 800,
					itemSize: 12,
					feature: {
						// 輔助線功能
						// mark : {show: true},
						dataView: {
							show: true
						},
						// 折線圖
						// magicType: {show: true, type: ['line', 'bar']},
						restore: {
							show: true
						},
						saveAsImage: {
							show: true
						}
					}
				},
				calculable: true,
				grid: {
					x: 100
				},
				dataZoom: {
					x: 15,
					zoomLock: true,
					orient: 'vertica',
					show: true,
					realtime: true,
					width: 20,
					start: 20,
					end: 100
				},
				xAxis: [{
					show: false,
					type: 'value',
					boundaryGap: [0, 0.01]
				}],
				yAxis: [{
					type: 'category',
					data: my_data100_name
				}],
				series: [{
					name: '測驗人數',
					type: 'bar',
					barMaxWidth: 10,
					itemStyle: {
						normal: {
							color: '#36a2ef',
							label: {
								show: true
							}
						}
					},
					data: my_data100_value
				}]
			};
			// 為echarts物件載入資料
			myChart.setOption(option);
			//點擊搜索
			$('#my_search').on('click', function() {
				var data2 = [{
					name: 'fdsaf',
					value: 1,
					num: 10
				}, {
					name: 'fdsaf',
					value: 2,
					num: 20
				}, {
					name: 'fdsaf',
					value: 3,
					num: 15
				}, {
					name: 'fdsaf',
					value: 4,
					num: 50
				}, {
					name: 'fdsaf',
					value: 5,
					num: 100
				}]
				if ($("input[value=js]").is(":checked")) {
					option.series[0].data = my_data100_value;
					option.yAxis[0].data = my_data100_name;
				} else {
					option.series[0].data = my_data100_num;
					option.yAxis[0].data = my_data100_name2;
				};
				option.dataZoom.end = iNowEnd;
				myChart.clear();
				myChart.setOption(option, true);
			})
			$('input[name=num]').on('click', function() {
				var val = $(this).val();
				if (val == 'ze') {
					option.dataZoom.end = iNowEnd;
					option.series[0].data = my_data100_num;
					option.yAxis[0].data = my_data100_name2;
					myChart.clear();
					myChart.setOption(option, true);
				} else {
					option.dataZoom.end = iNowEnd;
					option.series[0].data = my_data100_value;
					option.yAxis[0].data = my_data100_name;
					myChart.clear();
					myChart.setOption(option, true);
				}
			});
		});
});
</script>
</head>
	<body>
		<div class="data_wrap" style="background:#efeff5;width:950px;padding:0px 10px 10px 10px;">
		    <div class="animsition" style="overflow:hidden;animation-duration:1.5s;opacity:1;">
				 <div class="my_duxs_time">
						<div>
							更換顯示圖表：
							<label class="my_label">
								<input id="zjs" type="radio" checked="" value="js" name="num" aria-hidden="true" class="labelauty" style="display: none;">
								<label for="zjs"><span class="labelauty-unchecked-image"></span><span class="labelauty-unchecked"></span>
									<span class="labelauty-checked-image"></span>
									<span class="labelauty-checked"></span>
								</label>
								趨勢圖
							</label>
							<label class="my_label">
								<input type="radio" value="ze" name="num" aria-hidden="true" class="labelauty" id="labelauty-538255" style="display: none;">
								<label for="labelauty-538255">
									<span class="labelauty-unchecked-image"></span>
									<span class="labelauty-unchecked"></span>
									<span class="labelauty-checked-image"></span>
									<span class="labelauty-checked"></span>
								</label>
								圓餅圖
							</label>
						</div>
			    </div>
			    	<div id="main1" class="my_main2" style="width: 100%; height: 450px; float: left; -webkit-tap-highlight-color: transparent; user-select: none; background-color: white; cursor: default;" _echarts_instance_="1507880768066"><div style="position: relative; overflow: hidden; width: 1020px; height: 450px;"><div data-zr-dom-id="bg" class="zr-element" style="position: absolute; left: 0px; top: 0px; width: 1020px; height: 450px; user-select: none;"></div>
						<canvas width="1020" height="450" data-zr-dom-id="0" class="zr-element" style="position: absolute; left: 0px; top: 0px; width: 1020px; height: 450px; user-select: none; -webkit-tap-highlight-color: rgba(0, 0, 0, 0);"></canvas><canvas width="1020" height="450" data-zr-dom-id="1" class="zr-element" style="position: absolute; left: 0px; top: 0px; width: 1020px; height: 450px; user-select: none; -webkit-tap-highlight-color: rgba(0, 0, 0, 0);"></canvas>
						<canvas width="1020" height="450" data-zr-dom-id="_zrender_hover_" class="zr-element" style="position: absolute; left: 0px; top: 0px; width: 1020px; height: 450px; user-select: none; -webkit-tap-highlight-color: rgba(0, 0, 0, 0);"></canvas><div class="echarts-dataview" style="position: absolute; display: block; overflow: hidden; transition: height 0.8s, background-color 1s; z-index: 1; left: 0px; top: 0px; width: 1020px; height: 0px; background-color: rgb(240, 255, 255);"></div>
						<div class="echarts-tooltip zr-element" style="position: absolute; display: none; border-style: solid; white-space: nowrap; transition: left 0.4s, top 0.4s; background-color: rgba(50, 50, 50, 0.5); border-width: 0px; border-color: rgb(51, 51, 51); border-radius: 4px; color: rgb(255, 255, 255); font-family: 微軟雅黑, Arial, Verdana, sans-serif; padding: 5px; left: 201px; top: 167px;">名字0<br>人數 : 397</div></div></div>
		    </div>
		</div>
</body></html>
