<!DOCTYPE HTML>
<html>
<head>
<meta charset="UTF-8">
<title></title>
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
	//插件初始化
	$(":checkbox").labelauty({
		checked_label: "",
		unchecked_label: "",
	});
	$(":radio").labelauty({
		checked_label: "",
		unchecked_label: "",
	});
	//联动下拉
	$("#city_1").citySelect({
		url: {
			"citylist": [{
				"p": "新老客户",
				"c": [{
					"n": "不限"
				}, {
					"n": "新客户"
				}, {
					"n": "老客户"
				}]
			}, {
				"p": "级别",
				"c": [{
					"n": "LV-0"
				}, {
					"n": "LV-1"
				}, {
					"n": "LV-2"
				}, {
					"n": "LV-3"
				}, {
					"n": "LV-4"
				}]
			}]
		},
		prov: "新老客户",
		city: "不限"
	});
	$("#city_2").citySelect({
		url: {
			"citylist": [{
				"p": "全国省份排名",
				"c": [{
					"n": "HTML"
				}, {
					"n": "CSS",
					"a": [{
						"s": "CSS2.0"
					}, {
						"s": "CSS3.0"
					}]
				}, {
					"n": "JAVASCIPT"
				}]
			}, {
				"p": "编程语言",
				"c": [{
					"n": "C"
				}, {
					"n": "C++"
				}, {
					"n": "Objective-C"
				}, {
					"n": "PHP"
				}, {
					"n": "JAVA"
				}]
			}, {
				"p": "数据库",
				"c": [{
					"n": "Mysql"
				}, {
					"n": "SqlServer"
				}, {
					"n": "Oracle"
				}, {
					"n": "DB2"
				}]
			}, ]
		},
		prov: "全国省份排名",
		city: "",
		dist: "",
		nodata: "none"
	});
	//调用日期
	var start = {
		format: 'YYYY-MM-DD',
		//minDate: '2014-01-31', //设定最小日期为当前日期
		festival: true,
		//isinitVal:true,
		maxDate: $.nowDate(0), //最大日期
		choosefun: function(elem, datas) {
			end.minDate = datas; //开始日选好后，重置结束日的最小日期
			var iNow_maxDate = parseInt(datas.substring(0, 4));
			end.maxDate = datas.replace(new RegExp(iNow_maxDate, "g"),
				iNow_maxDate + 1);
		}
	};
	var end = {
		format: 'YYYY-MM-DD',
		//minDate: $.nowDate(0), //设定最小日期为当前日期
		festival: true,
		//isinitVal:true,
		maxDate: $.nowDate(0), //最大日期
		choosefun: function(elem, datas) {
			start.maxDate = datas; //将结束日的初始值设定为开始日的最大日期
			var iNow_minDate = parseInt(datas.substring(0, 4));
			start.minDate = datas.replace(new RegExp(iNow_minDate, "g"),
				iNow_minDate - 1);
		}
	};
	$("#inpstart").jeDate(start);
	$("#inpend").jeDate(end);
	//初始化切换
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
		console.log(my_data100_value)
	}
	// 路径配置
	require.config({
		paths: {
			echarts: 'http://echarts.baidu.com/build/dist'
		}
	});
	// 使用
	require(
		['echarts', 'echarts/chart/bar', 'echarts/chart/line',
			'echarts/chart/pie', 'echarts/chart/map', // 使用柱状图就加载bar模块，按需加载
		],
		function(ec) {
			// 基于准备好的dom，初始化echarts图表
			var myChart = ec.init(document.getElementById('main1'), 'macarons');
			var ecConfig = require('echarts/config');
			var option = {
				backgroundColor: 'white',
				title: {
					text: '排名前100',
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
					show: true,
					y: 15,
					x: 800,
					itemSize: 12,
					feature: {
						//mark : {show: true},
						dataView: {
							show: true
						},
						//magicType: {show: true, type: ['line', 'bar']},
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
					name: '人数',
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
			// 为echarts对象加载数据
			myChart.setOption(option);
			//点击搜索
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
	//关闭提示
	$('.xsfx_tips  i').on('click', function() {
		$(this).parent().slideUp();
	})
});
</script>
</head>
	<body style="">

		<div class="data_wrap" style="background: #efeff5; width: 1020px; padding: 10px;">
		    <div class="animsition" style="overflow: hidden; animation-duration: 1.5s; opacity: 1;">
				<div class="xsfx_tips">
					1.客户贡献分析用于自动判定在某一个时间段内在店铺贡献度最高的客户排名，该分析结果主要用于在某些活动中进行客户排名发放奖品等营销方式；<br>
					2.数据通过付款判定；<br>
					3.每笔交易中只要有一笔子订单发生了退款行为，则该交易不纳入核算；<br>
					4.手机号为收货人的手机号；
					<i class="fa fa-close"></i>
				</div>
				 <div class="my_duxs_time">
					付款时间：
					<input class="datainp wicon" id="inpstart" type="text" placeholder="开始日期" value="" readonly=""> ~
					<input class="datainp wicon" id="inpend" type="text" placeholder="结束日期" readonly="" style="margin-right: 30px;">

					备注旗帜：
					<label class="my_label"><input type="checkbox" value="qz1" name="qz1" aria-hidden="true" class="labelauty" id="labelauty-341616" style="display: none;"><label for="labelauty-341616"><span class="labelauty-unchecked-image"></span><span class="labelauty-unchecked"></span><span class="labelauty-checked-image"></span><span class="labelauty-checked"></span></label> <i class="fa fa-flag"></i></label>
					<label class="my_label"><input type="checkbox" value="qz2" name="qz2" aria-hidden="true" class="labelauty" id="labelauty-596838" style="display: none;"><label for="labelauty-596838"><span class="labelauty-unchecked-image"></span><span class="labelauty-unchecked"></span><span class="labelauty-checked-image"></span><span class="labelauty-checked"></span></label> <i class="fa fa-flag"></i></label>
					<label class="my_label"><input type="checkbox" value="qz3" name="qz3" aria-hidden="true" class="labelauty" id="labelauty-1007586" style="display: none;"><label for="labelauty-1007586"><span class="labelauty-unchecked-image"></span><span class="labelauty-unchecked"></span><span class="labelauty-checked-image"></span><span class="labelauty-checked"></span></label> <i class="fa fa-flag"></i></label>
					<label class="my_label"><input type="checkbox" value="qz4" name="qz4" aria-hidden="true" class="labelauty" id="labelauty-347163" style="display: none;"><label for="labelauty-347163"><span class="labelauty-unchecked-image"></span><span class="labelauty-unchecked"></span><span class="labelauty-checked-image"></span><span class="labelauty-checked"></span></label> <i class="fa fa-flag"></i></label>
					<label class="my_label"><input type="checkbox" value="qz5" name="qz5" aria-hidden="true" class="labelauty" id="labelauty-595327" style="display: none;"><label for="labelauty-595327"><span class="labelauty-unchecked-image"></span><span class="labelauty-unchecked"></span><span class="labelauty-checked-image"></span><span class="labelauty-checked"></span></label> <i class="fa fa-flag"></i></label>
					<label class="my_label"><input type="checkbox" value="qz5" name="qz5" aria-hidden="true" class="labelauty" id="labelauty-908482" style="display: none;"><label for="labelauty-908482"><span class="labelauty-unchecked-image"></span><span class="labelauty-unchecked"></span><span class="labelauty-checked-image"></span><span class="labelauty-checked"></span></label> <i class="fa fa-flag"></i></label>
					<button id="my_search" class="my_btn">搜索</button>
					<div class="chebox_wrap">
						数据图表：
						<label class="my_label"><input id="zjs" type="radio" checked="" value="js" name="num" aria-hidden="true" class="labelauty" style="display: none;"><label for="zjs"><span class="labelauty-unchecked-image"></span><span class="labelauty-unchecked"></span><span class="labelauty-checked-image"></span><span class="labelauty-checked"></span></label> 购买总件数</label>
						<label class="my_label"><input type="radio" value="ze" name="num" aria-hidden="true" class="labelauty" id="labelauty-538255" style="display: none;"><label for="labelauty-538255"><span class="labelauty-unchecked-image"></span><span class="labelauty-unchecked"></span><span class="labelauty-checked-image"></span><span class="labelauty-checked"></span></label> 消费总额</label>

					</div>
			    </div>
			    	<div id="main1" class="my_main2" style="width: 100%; height: 450px; float: left; -webkit-tap-highlight-color: transparent; user-select: none; background-color: white; cursor: default;" _echarts_instance_="1507880768066"><div style="position: relative; overflow: hidden; width: 1020px; height: 450px;"><div data-zr-dom-id="bg" class="zr-element" style="position: absolute; left: 0px; top: 0px; width: 1020px; height: 450px; user-select: none;"></div><canvas width="1020" height="450" data-zr-dom-id="0" class="zr-element" style="position: absolute; left: 0px; top: 0px; width: 1020px; height: 450px; user-select: none; -webkit-tap-highlight-color: rgba(0, 0, 0, 0);"></canvas><canvas width="1020" height="450" data-zr-dom-id="1" class="zr-element" style="position: absolute; left: 0px; top: 0px; width: 1020px; height: 450px; user-select: none; -webkit-tap-highlight-color: rgba(0, 0, 0, 0);"></canvas><canvas width="1020" height="450" data-zr-dom-id="_zrender_hover_" class="zr-element" style="position: absolute; left: 0px; top: 0px; width: 1020px; height: 450px; user-select: none; -webkit-tap-highlight-color: rgba(0, 0, 0, 0);"></canvas><div class="echarts-dataview" style="position: absolute; display: block; overflow: hidden; transition: height 0.8s, background-color 1s; z-index: 1; left: 0px; top: 0px; width: 1020px; height: 0px; background-color: rgb(240, 255, 255);"></div><div class="echarts-tooltip zr-element" style="position: absolute; display: none; border-style: solid; white-space: nowrap; transition: left 0.4s, top 0.4s; background-color: rgba(50, 50, 50, 0.5); border-width: 0px; border-color: rgb(51, 51, 51); border-radius: 4px; color: rgb(255, 255, 255); font-family: 微软雅黑, Arial, Verdana, sans-serif; padding: 5px; left: 201px; top: 167px;">名字0<br>人数 : 397</div></div></div>
			    	<button id="my_dc" class="my_btn">导出</button>
		    </div>

		</div>
</body></html>
