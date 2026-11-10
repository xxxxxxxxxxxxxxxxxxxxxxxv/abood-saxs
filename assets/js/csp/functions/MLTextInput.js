/**
 * MLTextInput() function JS
 *
 * @since 12.5
 *
 * @package RosarioSIS
 */

csp.functions.mlTextInput = {
	setMLvalue: function() {
		var value = this.value,
			loc = this.dataset.loc,
			resEl = document.getElementById(this.dataset.id),
			res = resEl.value.split('|');

		if (loc === '') {
			resEl.value = value;

			return;
		}

		var found = 0;

		for ( var i = 1; i < res.length; i++ ) {
			if (res[i].substring(0, loc.length) == loc) {
				found = 1;

				if (value === '') {
					for ( var j = i + 1; j < res.length; j++ ) {
						res[j - 1] = res[j];
					}

					res.pop();
				} else {
					res[i] = loc + ':' + value;
				}
			}
		}

		if (! found && (value !== '')) {
			res.push(loc + ':' + value);
		}

		resEl.value = res.join('|');
	},
	onEvents: function() {
		$('.ml-text-input').on('change', 'input', csp.functions.mlTextInput.setMLvalue);
	}
}

$(csp.functions.mlTextInput.onEvents);
