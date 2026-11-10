/**
 * Student Schedule program (Scheduling module) JS
 *
 * @since 12.5
 *
 * @package RosarioSIS
 */

csp.modules.scheduling.schedule = {
	horizontalFormatSwitch: function() {
		if (this.checked) {
			$('#printSchedulesLink')[0].href += '&horizontalFormat';
		} else {
			$('#printSchedulesLink')[0].href = $('#printSchedulesLink')[0].href.replace('&horizontalFormat', '');
		}
	},
	timeTableSwitch: function() {
		if ($('#schedule_table')[0].checked) {
			$('#printSchedulesLink')[0].href = $('#printSchedulesLink')[0].href.replace('Yes', 'No');
		} else {
			$('#printSchedulesLink')[0].href = $('#printSchedulesLink')[0].href.replace('No', 'Yes');
		}
	},
	switchLock: function() {
		if (this.src.indexOf('unlocked') == -1) {
			this.src = this.src.replace('locked', 'unlocked');

			this.title = this.alt = this.dataset.unlocked;

			$(this).next('input').val('');
		} else {
			this.src = this.src.replace('unlocked', 'locked');

			this.title = this.alt = this.dataset.locked;

			$(this).next('input').val('Y');
		}
	},
	onEvents: function() {
		$('.onchange-print-schedule-format').on('change', csp.modules.scheduling.schedule.horizontalFormatSwitch);

		$('.onchange-print-schedule-table').on('change', csp.modules.scheduling.schedule.timeTableSwitch);

		$('.onclick-switch-lock').on('click', csp.modules.scheduling.schedule.switchLock);
	},
	ready: function() {
		// @since 12.0 Use colorBox instead of popup window
		if ($('#opener_url').length) {
			// Note: No need to close colorBox as ajaxLink() will display results in #body
			ajaxLink($('#opener_url').val());
		} else {
			csp.modules.scheduling.schedule.onEvents();
		}
	}
}

$(csp.modules.scheduling.schedule.ready);
