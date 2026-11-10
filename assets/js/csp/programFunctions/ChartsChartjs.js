/**
 * ChartjsChart() ProgramFunctions JS
 *
 * @since 12.5
 *
 * @package RosarioSIS
 */

csp.programFunctions.chartsChartjs = function() {
	/**
	 * Instanciate Chart.js
	 * Use `data-legend-display`, `data-title`, `data-options`,
	 * `data-type`, `data-labels`, `data-datasets` HTML attributes
	 *
	 * @uses Chart() object
	 * @link https://www.chartjs.org/docs/latest/
	 */
	var instanciate = function() {
		Chart.defaults.font.size = 14;

		// Configuration options go here.
		var chartOptions = {
			responsive: true,
			// Canvas aspect ratio (i.e. width / height, a value of 1 representing a square canvas).
			aspectRatio: 2, // Fix for Pie charts height to big.
			plugins: {
				// Chart Options: Show legend on the right.
				legend: {
					position: "right",
					display: $(this).data('legendDisplay')
				},
				title: {
					display: true,
					font: {
						size: 16
					},
					text: $(this).data('title')
				}
			}
		};

		// @link https://stackoverflow.com/questions/171251/how-can-i-merge-properties-of-two-javascript-objects
		$.extend(chartOptions, $(this).data('options'));

		var chart = new Chart(
			this.getContext('2d'), {
			// The type of chart we want to create.
			type: $(this).data('type'),
			// The data for our dataset.
			data: {
				labels: $(this).data('labels'),
				datasets: $(this).data('datasets')
			},
			options: chartOptions
		});

		csp.programFunctions.chartsChartjs.charts.push(chart);
	}

	/**
	 * Charts array to store charts
	 *
	 * @type {Array}
	 *
	 * @visibility public
	 */
	csp.programFunctions.chartsChartjs.charts = [];

	$('.chart canvas').each(instanciate);
}

$(csp.programFunctions.chartsChartjs);

