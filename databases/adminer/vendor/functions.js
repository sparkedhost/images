'use strict';

/** Get first element by selector
* @param {string} selector
* @param {HTMLElement} [context]
* @return {HTMLElement}
*/
function qs(selector, context = document) {
	return context.querySelector(selector);
}

/** Get last element by selector
* @param {string} selector
* @param {HTMLElement} [context=document]
* @return {HTMLElement}
*/
function qsl(selector, context) {
	const els = qsa(selector, context);
	return els[els.length - 1];
}

/** Get all elements by selector
* @param {string} selector
* @param {HTMLElement} [context]
* @return {NodeList}
*/
function qsa(selector, context = document) {
	return context.querySelectorAll(selector);
}

/** Create an element
* @param {string} tag
* @param {Object} [props] properties including event handlers, e.g. {className: 'x', onclick: fn}
* @param {...(Node|string)} content strings are inserted as text
* @return {HTMLElement}
*/
function ce(tag, props = {}, ...content) {
	const el = document.createElement(tag);
	mixin(el, props);
	el.append(...content);
	return el;
}

/** Return a function calling fn with the next arguments
* @param {function} fn
* @param {...*} args
* @return {function} with preserved this
*/
function partial(fn, ...args) {
	return function () {
		return fn.apply(this, args);
	};
}

/** Assign values from source to target
* @param {Object} target
* @param {Object} source
*/
function mixin(target, source) {
	// a shortcut for Object.assign() saving bytes in the inline scripts, used also by external plugins
	Object.assign(target, source);
}

/** Add or remove CSS class
* @param {?HTMLElement} el
* @param {string} className
* @param {boolean} [enable]
*/
function alterClass(el, className, enable) {
	if (el) {
		el.classList.toggle(className, !!enable); // !! - undefined would toggle
	}
}

/** Toggle visibility
* @param {string} id
* @return {boolean} false
*/
function toggle(id) {
	const el = qs('#' + id);
	el && el.classList.toggle('hidden');
	return false;
}

/** Escape string to use in a query string; counterpart of PHP url_escape()
* @param {string} string
* @return {string}
*/
function urlEscape(string) {
	// unlike encodeURIComponent(), escapes only the characters misinterpreted by PHP or by the URL syntax and those rewritten by the browser
	return encodeURIComponent(string)
		.replace(/'/g, '%27') // encodeURIComponent() keeps it but the browser escapes it anyway
		.replace(/%20/g, '+')
		// $,/:;@[\]^`{|} - the browser keeps them verbatim; urlSeparators are set by arg_separator.input
		.replace(/%(24|2C|2F|3A|3B|40|5[BCDE]|60|7[BCD])/g, (all, hex) => {
			const char = String.fromCharCode(parseInt(hex, 16));
			return (urlSeparators.indexOf(char) < 0 ? char : all);
		})
	;
}

/** Get value from a query string; counterpart of PHP urldecode()
* @param {string} string
* @return {string}
*/
function urlUnescape(string) {
	return decodeURIComponent(string.replace(/\+/g, ' '));
}

/** Serialize form fields to a query string
* @param {HTMLFormElement} form
* @param {HTMLElement} [submitter] the button which sent the form
* @param {boolean} [defaults] skip the fields holding the value from their data-default
* @return {?string} null if the form contains a file
*/
function formData(form, submitter, defaults) {
	const data = [];
	for (const el of form.elements) {
		if (el.name && !el.disabled) {
			if (/^file$/i.test(el.type) && el.value) {
				return null;
			}
			if (!/^(checkbox|radio|submit|file)$/i.test(el.type) || el.checked || el == submitter) {
				const value = selectValue(el);
				if (!defaults || el.getAttribute('data-default') !== value) { // getAttribute() returns null for a field which must be always sent
					data.push(urlEscape(el.name) + '=' + urlEscape(value));
				}
			}
		}
	}
	return data.join('&');
}

/** Set permanent cookie
* @param {string} assign
* @param {number} days
*/
function cookie(assign, days) {
	const date = new Date();
	date.setDate(date.getDate() + days);
	document.cookie = assign
		+ '; expires=' + date
		+ '; path=' + location.pathname.replace(/[;,]/g, encodeURIComponent) // default path is without the trailing slash
		+ (location.protocol == 'https:' ? '; secure' : '') // the same attributes as in the server-side cookie(), HttpOnly can't be used because this cookie is read by JavaScript
		+ '; SameSite=lax'
	;
}

/** Verify current Adminer version
* @param {string} current
*/
function verifyVersion(current) {
	cookie('adminer_version=0', 1);
	ajax('https://www.adminer.org/version/?current=' + current, request => {
		const json = JSON.parse(request.responseText);
		cookie('adminer_version=' + (json.version || current), 7); // empty if there's no newer version
		qs('#version').textContent = json.version;
	}, '', null); // null - a failed check must not report being offline
}

/** Get value of select
* @param {HTMLElement} select <select> or <input>
* @return {string}
*/
function selectValue(select) {
	if (!select.selectedIndex) {
		return select.value;
	}
	const selected = select.options[select.selectedIndex];
	return ((selected.attributes.value || {}).specified ? selected.value : selected.text);
}

/** Set checked class
* @param {HTMLInputElement} el
*/
function trCheck(el) {
	const tr = el.closest('tr');
	alterClass(tr, 'checked', el.checked);
	fire(el.form && el.form['all'], 'click'); // the element named all counts the selected items, its handler is registered by a data attribute
}

/** Group digits in a number
* @param {string|number} number
* @return {string}
* @uses numberFormat
* @uses numberDigits
*/
function formatNumber(number) {
	const match = /^#+([^#0]+)(?:(#+)\1)?(#*0)$/.exec(numberFormat); // CLDR pattern: separator, group repeated to the left, rightmost group
	const size = match[3].length;
	// \B doesn't match after the sign
	return (number + '').replace(RegExp('\\B(?=(\\d{' + ((match[2] || '').length || size) + '})*\\d{' + size + '}$)', 'g'), match[1])
		.replace(/\d/g, digit => numberDigits[digit]);
}

/** Fill number of selected items
* @param {string} id
* @param {string|number} count
*/
function selectCount(id, count) {
	setHtml(id, (count === '' ? '' : '(' + formatNumber(count) + ')'));
	const el = qs('#' + id);
	if (el) {
		for (const input of qsa('input[type=submit]', el.parentNode.parentNode)) {
			input.disabled = (count == '0');
		}
		fire(document.activeElement, 'focus'); // the default submit button changes with the disabled buttons
	}
}

/** Check all elements matching given name
* @param {string} name regular expression
* @this HTMLInputElement
*/
function formCheck(name) {
	const re = new RegExp(name); // string - a regular expression can't be passed in a data attribute
	for (const elem of this.form.elements) {
		if (re.test(elem.name)) {
			elem.checked = this.checked;
			trCheck(elem);
		}
	}
}

/** Check all rows in <table class="checkable">  */
function tableCheck() {
	qsa('table.checkable td:first-child input').forEach(trCheck);
	onpageshow = tableCheck; // once the browser restores the checkboxes while browsing history
}

/** Uncheck single element
* @param {string} id
*/
function formUncheck(id) {
	const el = qs('#' + id);
	el.checked = false;
	trCheck(el);
}

/** Get number of checked elements matching given name
* @param {HTMLInputElement} input
* @param {RegExp} name
* @return {number}
*/
function formChecked(input, name) {
	return [...input.form.elements].filter(el => name.test(el.name) && el.checked).length;
}

/** Fill number of selected databases
* @this HTMLInputElement
*/
function countDbs() {
	selectCount('selected', formChecked(this, /^db/));
}

/** Fill numbers of selected tables
* @param {number} tables number of tables in the database
* @this HTMLInputElement
*/
function countTables(tables) {
	const checked = formChecked(this, /^(tables|views)\[/);
	selectCount('selected', checked);
	selectCount('selected2', formChecked(this, /^tables\[/) || tables); // search is performed in all tables if none is selected
	selectCount('selected3', checked);
}

/** Fill numbers of selected rows
* @param {string} rows number of rows in the whole result
* @this HTMLInputElement
*/
function countRows(rows) {
	const checked = formChecked(this, /^check/);
	selectCount('selected', this.checked ? rows : checked);
	selectCount('selected2', this.checked || !checked ? rows : checked); // the command is performed on the whole result if no row is selected
}

/** Ask for confirmation before submit
* @param {string} message
* @return {boolean} false to cancel the submit
*/
function confirmClick(message) {
	// window.confirm can't be registered as the handler itself - it throws Illegal invocation when apply() passes the element as this
	return confirm(message);
}

/** Select clicked row
* @param {MouseEvent} event
*/
function tableClick(event) {
	const td = event.target.closest('td');
	let text;
	if (td && (text = td.dataset.text)) {
		if (selectClick.call(td, event, +text, td.dataset.warning)) {
			return;
		}
	}
	// dblclick undoes the check by the first click of the double click - the user is only selecting a word
	let click = (event.type == 'dblclick' || getSelection().isCollapsed);
	let el = event.target.closest('tr, table, a, input, textarea'); // the other elements handle the click themselves
	if (el && !el.matches('tr')) {
		if (el.type != 'checkbox') {
			return;
		}
		checkboxClick.call(el, event);
		click = false;
		el = el.closest('tr');
	}
	if (!el) { // Ctrl+click on text fields hides the element
		return;
	}
	el = qs('input[type=checkbox]', el.firstChild); // not firstChild - the first cell of select also contains the edit link
	if (!el) { // the header row of the process list and of the database list has an empty first cell
		return;
	}
	if (click) {
		el.checked = !el.checked;
		fire(el, 'click'); // the handler can be registered by a data attribute
	}
	if (el.name == 'check[]') {
		el.form['all'].checked = false;
		formUncheck('all-page');
	}
	if (/^(tables|views)\[\]$/.test(el.name)) {
		formUncheck('check-all');
	}
	trCheck(el);
}

let lastChecked;

/** Shift-click on checkbox for multiple selection.
* @param {MouseEvent} event
* @this HTMLInputElement
*/
function checkboxClick(event) {
	if (!this.name) {
		return;
	}
	if (event.shiftKey && (!lastChecked || lastChecked.name == this.name)) {
		const checked = (lastChecked ? lastChecked.checked : true);
		let checking = !lastChecked;
		for (const input of qsa('input', this.closest('table'))) {
			if (input.name === this.name) {
				if (checking) {
					input.checked = checked;
					trCheck(input);
				}
				if (input === this || input === lastChecked) {
					if (checking) {
						break;
					}
					checking = true;
				}
			}
		}
	} else {
		lastChecked = this;
	}
}

/** Set HTML code of an element
* @param {string} id
* @param {?string} html undefined to set parentNode to empty string
*/
function setHtml(id, html) {
	const el = qs('[id="' + id.replace(/[\\"]/g, '\\$&') + '"]'); // database name is used as ID
	if (el) {
		if (html == null) {
			el.parentNode.innerHTML = '';
		} else {
			el.innerHTML = html;
		}
	}
}



/** Display items in menu
* @param {MouseEvent} event
* @this HTMLElement
*/
function menuOver(event) {
	const a = event.target;
	if (a.matches('a, span') && a.offsetLeft + a.offsetWidth > a.parentNode.offsetWidth) {
		this.style.overflow = 'visible';
	}
}

/** Hide items in menu
* @this HTMLElement
*/
function menuOut() {
	this.style.overflow = 'hidden';
}

/** Toggle the menu on small screens */
function menuToggle() {
	const foot = qs('#foot');
	const opened = !foot.classList.toggle('foot');
	qs('#menuopen button').setAttribute('aria-expanded', opened);
	if (opened) {
		foot.tabIndex = -1; // to make it focusable
		foot.focus(); // to continue tabbing in the menu
	}
}

/** Close the menu on small screens */
function menuClose() {
	const foot = qs('#foot');
	if (foot && !foot.classList.contains('foot')) {
		foot.classList.add('foot');
		const button = qs('#menuopen button');
		button.setAttribute('aria-expanded', false);
		if (foot.contains(document.activeElement)) { // the focused element is hidden now
			button.focus();
		}
	}
}



/** Submit the form of the changed field
* @this HTMLElement
*/
function formSubmit() {
	this.form.submit();
}

/** Disable Save and continue editing after changing a value identifying the row
* @param {Event} event
* @this HTMLTableRowElement
*/
function whereChange(event) {
	// isTrusted - fire() dispatches change to apply the initially selected function, which is not a change by the user
	if (event.isTrusted !== false) {
		// the WHERE condition in the URL is not updated by the AJAX save so the next save would not match the row
		qs('#form [name=insert]').disabled = true;
	}
}

/** Add row in select fieldset
* @this HTMLSelectElement
*/
function selectAddRow() {
	const field = this;
	const row = cloneNode(field.parentNode); // the clone keeps the attribute so that it adds the next row
	field.setAttribute('data-onchange', 'selectFieldChange()');
	fire(field, 'change');
	for (const select of qsa('select', row)) {
		select.name = select.name.replace(/[a-z]\[\d+/, '$&1');
		select.selectedIndex = 0;
	}
	for (const input of qsa('input', row)) {
		input.name = input.name.replace(/[a-z]\[\d+/, '$&1');
		input.className = '';
		if (input.type == 'checkbox') {
			input.checked = false;
		} else {
			input.value = '';
		}
	}
	field.parentNode.parentNode.append(row);
}

/** Rerun the handler of the first field in the row, it checks whether an index will be used
* @this HTMLElement
*/
function selectFirstChange() {
	fire(this.parentNode.firstChild, 'change');
}

/** Prevent the search handler on Enter, clear the field by Esc
* @param {KeyboardEvent} event
* @this HTMLInputElement
*/
function selectSearchKeydown(event) {
	if (event.key == 'Enter') {
		this.removeAttribute('data-onsearch');
	} else if (isEscape(event)) {
		// the same as clicking the cancel button; Firefox displays none and dispatches no search event, WebKit does both and repeats this
		this.value = '';
		selectSearchSearch.call(this);
	}
}

/** Clear column name after resetting search
* @this HTMLInputElement
*/
function selectSearchSearch() {
	if (!this.value) {
		this.parentNode.firstChild.value = ''; // also an input typed by a plugin
	}
}



/** Fill column in search field
* @param {string} name
* @return {boolean} false
*/
function selectSearch(name) {
	const fieldset = qs('#fieldset-search');
	fieldset.className = '';
	const divs = qsa('div', fieldset);
	let div = [...divs].find(row => {
		const col = qs('[name$="[col]"]', row);
		return col && selectValue(col) == name;
	});
	if (!div) { // use the last empty row
		div = divs[divs.length - 1];
		div.firstChild.value = name;
		fire(div.firstChild, 'change');
	}
	qs('[name$="[val]"]', div).focus();
	return false;
}



const isMac = /^(Mac|iP)/.test(navigator.platform);

/** Check if Ctrl key (Command key on Mac) was pressed
* @param {KeyboardEvent|MouseEvent} event
* @return {boolean}
*/
function isCtrl(event) {
	// not Ctrl on Mac - Ctrl+click opens the context menu and Ctrl+K deletes to the end of the line in text fields
	return (event.metaKey || (!isMac && event.ctrlKey)) && !event.altKey; // shiftKey allowed
}

/** Write the keyboard shortcuts in titles in the Mac notation */
function macShortcuts() {
	for (const el of qsa('a[title], input[title], fieldset[title]')) { // not all elements - e.g. a comment in a title could contain Ctrl
		el.title = el.title
			.replace(/^Alt\+Shift\+/, '⌃⌥') // access keys
			.replace(/^Ctrl\+(Shift\+)?Enter$/, (all, shift) => (shift ? '⇧' : '') + '⌘↩')
			.replace(/\bCtrl\b/, '⌘') // the translations of Ctrl+click
		;
	}
}

/** Check if Esc was pressed alone
* @param {KeyboardEvent} event
* @return {boolean}
*/
function isEscape(event) {
	return event.key == 'Escape' && !event.shiftKey && !event.altKey && !isCtrl(event);
}



/** Send form by Ctrl+Enter on <select>, <textarea> and <input>
* @param {string} button name of the submit button to click, empty for the implicit one
* @param {KeyboardEvent} event
* @return {boolean} false if the form was sent
*/
function submitKeydown(button, event) {
	let target = event.target;
	if (target.jushTextarea) {
		target = target.jushTextarea;
	}
	if (isCtrl(event) && event.key == 'Enter' && target.matches('select, textarea, input')) {
		target.blur();
		// click the button highlighted as the default one to send its name and to run its onclick handler
		const submit = (button ? target.form[button] : findDefaultSubmit(target));
		if (submit) {
			submit.click();
		} else if (fire(target.form, 'submit')) { // submit() doesn't dispatch the event
			target.form.submit();
		}
		target.focus();
		return false;
	}
}

/** Close the menu by Esc, send form by Ctrl+Enter
* @param {KeyboardEvent} event
* @return {boolean}
*/
function bodyKeydown(event) {
	if (isEscape(event)) {
		menuClose();
	}
	if (jumpShortcut(event) === false) {
		return false;
	}
	if (!delegateEvent(event)) { // a delegated handler sends the form by its own button
		return submitKeydown('', event);
	}
}

/** Toggle visibility by .toggle links, keep the AJAX message on the screen, close the menu, open form to a new window on Ctrl+click or Shift+click
* @param {MouseEvent} event
*/
function bodyClick(event) {
	delegateEvent(event);
	const target = event.target;
	const toggler = target.closest && target.closest('.toggle'); // closest && - the target can be a text node, e.g. from fire()
	if (toggler) {
		toggle(toggler.getAttribute('href').slice(1));
		event.preventDefault();
	}
	if (target.closest && target.closest('#ajaxstatus')) { // the user works with the message, keep it on the screen
		clearTimeout(stickyTimeout);
	}
	if ((isCtrl(event) || event.shiftKey) && target.type == 'submit' && target.matches('input')) { // type - the target can be a text node without matches()
		target.form.target = '_blank';
		setTimeout(() => {
			// if (isCtrl(event)) { focus(); } doesn't work
			target.form.target = '';
		}, 0);
	}
	const foot = qs('#foot'); // null if fire() dispatches a click before the menu is printed at the end of the page
	if (foot && !foot.contains(target) && !qs('#menuopen').contains(target)) { // menuopen - delegateEvent() has toggled the menu by it already
		menuClose();
	}
}

/** Highlight the value exceeding the maximum length
* @param {HTMLInputElement} el
*/
function maxlengthCheck(el) {
	const maxLength = el.dataset.maxlength;
	alterClass(el, 'maxlength', el.value && maxLength != null && el.value.length > maxLength); // maxLength could be 0
}

/** Send GET forms with minimal URL escaping
* @param {SubmitEvent} event
* @return {boolean} false if the form was sent by JS
*/
function bodySubmit(event) {
	if (delegateEvent(event)) { // a data-onsubmit handler has handled the event
		return true;
	}
	const form = event.target;
	if (/^get$/i.test(form.method)) { // the browser would serialize the form with full escaping
		const data = formData(form, event.submitter, true); // submitter is undefined in Chrome < 81, no GET form uses a named submit button
		if (data !== null) {
			const url = form.action.replace(/\?.*/, '') + '?' + data;
			if (form.target) { // set by Ctrl+click or Shift+click; open() is not blocked, the click activation is still valid
				open(url, form.target);
			} else {
				location.href = url;
			}
			return false;
		}
	}
}

/** Highlight too long values
* @param {InputEvent} event
*/
function bodyInput(event) {
	delegateEvent(event);
	maxlengthCheck(event.target);
}

/** Call handlers registered by data-on<event> attributes between the event target and the <html> element
* @param {Event} event
* @return {boolean} true if a handler has handled the event, never false - it is registered by a property, which would preventDefault()
*/
function delegateEvent(event) {
	const attr = 'data-on' + event.type;
	const selector = '[' + attr + ']';
	// closest() - much faster than getAttribute() on every ancestor, target.closest - the target can be a text node
	let el = event.target.closest && event.target.closest(selector);
	while (el) {
		const value = el.getAttribute(attr);
		const match = /^(\w+)\((.*)\)$/.exec(value); // e.g. confirmClick("Are you sure?")
		// only a function declared by Adminer or by a plugin - it creates a non-configurable property of window, built-ins are configurable or not own properties at all, so window['eval'] or window['setTimeout'] is never used
		const desc = (match ? Object.getOwnPropertyDescriptor(window, match[1]) : null);
		const handler = (desc && !desc.configurable && typeof desc.value == 'function' ? desc.value : null);
		let args;
		try {
			args = (handler ? JSON.parse('[' + match[2] + ']') : null); // JSON.parse() - the arguments must never be evaluated as code
		} catch (e) { // empty - an invalid value is reported below together with an unknown handler
		}
		if (args) {
			const result = handler.apply(el, args.concat(event));
			if (result === false) {
				event.preventDefault();
			}
			if (result !== undefined) {
				return true; // the handler handled the event, don't call the handlers on ancestor elements
			}
		} else {
			console.error('Invalid handler in ' + attr + "='" + value + "'", el); // not lang() - it reports a bug in Adminer or in a plugin, the element would do nothing at all otherwise
		}
		el = el.parentElement && el.parentElement.closest(selector); // parentElement - closest() starts at the element itself
	}
}



/** Change focus by Ctrl+Shift+Up or Ctrl+Shift+Down
* @param {KeyboardEvent} event
* @return {boolean}
*/
function editingKeydown(event) {
	if (/^Arrow(Down|Up)$/.test(event.key) && isCtrl(event)) {
		const target = event.target;
		const cell = target.closest('td, th'); // not parentNode - the NULL checkbox is wrapped in a <label>
		const cells = [...cell.parentNode.children];
		const sibling = (event.key == 'ArrowUp' ? 'previousElementSibling' : 'nextElementSibling');
		let row = cell.parentNode;
		do {
			row = row[sibling];
		} while (row && row.hidden); // skip removed columns
		const cell2 = (row ? row.children[cells.indexOf(cell)] : null);
		if (cell2) {
			// the element at the same position can be hidden, the cell displays e.g. collation or ON DELETE by the column type
			const name = target.name.replace(/.*(\[[^[]+])$/, '$1');
			const el = [qs('[name$="' + name + '"]', cell2), ...qsa('input, select, textarea, button', cell2)].find(el2 => el2 && el2.offsetParent);
			if (el) {
				el.focus();
			}
		}
		return false;
	}
	if (event.shiftKey) {
		return submitKeydown('insert', event);
	}
}

/** Replace the value field by a plain <input> or <textarea> for the SQL function, hide it for functions without an argument and disable maxlength for other functions
* @this HTMLSelectElement
*/
function functionChange() {
	const td = this.parentNode.nextSibling;
	const name = this.name.replace(/^function/, 'fields');
	const func = selectValue(this);
	const noArg = /^(now|getdate|current_date|current_timestamp|uuid)$/.test(func); // these functions take no argument
	if (func == 'SQL' || noArg) {
		if (!td.origNodes) { // move away the whole content, e.g. a syntax highlighter, <select> from the edit-foreign plugin or Editor's hint
			const input = Array.from(this.form.elements).find(input =>
				input.name === name && input.type !== 'hidden'
			); // undefined with the set data type
			const textarea = (input && input.tagName == 'TEXTAREA');
			const text = (textarea
				? ce('textarea', {name, rows: input.rows, cols: input.cols})
				: ce('input', {name, size: 40})
			);
			text.value = (input ? selectValue(input) : '');
			setupSubmitHighlightInput(text);
			td.sqlInput = text;
			td.origNodes = document.createDocumentFragment();
			td.origNodes.append(...td.childNodes);
			td.append(text);
		}
		alterClass(td.sqlInput, 'hidden', noArg); // still sent, process_input() skips a missing field
	} else {
		if (td.origNodes) { // revive the original content (keeps its type, e.g. number for +)
			td.textContent = '';
			td.append(td.origNodes);
			delete td.origNodes;
			delete td.sqlInput;
		}
		const input = this.form[name];
		if (input && input.tagName) { // undefined with the set data type, RadioNodeList with boolean (a hidden input and a checkbox)
			if (func) { // the length is not limited when a function is applied
				if (input.origMaxLength === undefined) {
					input.origMaxLength = input.dataset.maxlength;
				}
				delete input.dataset.maxlength;
			} else if (input.origMaxLength >= 0) {
				input.dataset.maxlength = input.origMaxLength;
				delete input.origMaxLength;
			}
			maxlengthCheck(input); // not fire() - the input event would also run skipOriginal() on the parent <td>
		}
	}
	helpClose();
}

/** Skip 'original' when typing
* @param {number} first
* @this HTMLTableCellElement
*/
function skipOriginal(first) {
	const fnSelect = qs('select', this.previousSibling);
	if (fnSelect.selectedIndex < first) {
		fnSelect.selectedIndex = first;
	}
}

/** Add new field in schema-less edit
* @this HTMLInputElement
*/
function fieldChange() {
	const tr = this.closest('tr');
	const row = cloneNode(tr);
	for (const input of qsa('input', row)) {
		input.value = '';
	}
	// keep value in <select> (function)
	tr.parentNode.append(row);
	this.removeAttribute('data-oninput'); // the appended row adds the next one
}



/** Create AJAX request
* @param {string} url
* @param {function(XMLHttpRequest)} callback
* @param {?string} [data]
* @param {?string} [message] null to not report errors
* @return {XMLHttpRequest}
* @uses offlineMessage
*/
function ajax(url, callback, data, message) {
	const request = new XMLHttpRequest();
	// empty the live region instead of hiding it, display: none would remove it from the accessibility tree
	ajaxStatus(message ? '<div class="message">' + message + '</div>' : '');
	request.open((data ? 'POST' : 'GET'), url);
	if (data) {
		request.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
	}
	if (new URL(url, location).origin == location.origin) { // cross-origin would be preflighted and is_ajax() is only ours
		request.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
	}
	request.onreadystatechange = () => {
		if (request.readyState == 4) {
			if (/^2/.test(request.status)) {
				callback(request);
			} else if (message !== null) {
				ajaxStatus(request.status ? request.responseText : '<div class="error">' + offlineMessage + '</div>');
			}
		}
	};
	request.send(data);
	return request;
}

/** Use setHtml(key, value) for JSON response
* @param {string} url
* @return {boolean} false
*/
function ajaxSetHtml(url) {
	ajax(url, request => {
		const data = JSON.parse(request.responseText);
		for (const key in data) {
			setHtml(key, data[key]);
		}
	});
	return false;
}

let editChanged; // used by plugins
let adminerHighlighter = () => {}; // overwritten by syntax highlighters

/** Save form contents through AJAX
* @param {string} message
* @return {boolean} false when sent by AJAX
* @this HTMLInputElement submit button
*/
function ajaxForm(message) {
	const button = this;
	const form = button.form;
	let data = formData(form, button);
	if (data === null) {
		return true; // files can't be sent by AJAX, submit the form
	}

	let url = form.action;
	if (!/post/i.test(form.method)) {
		url = url.replace(/\?.*/, '') + '?' + data;
		data = '';
	}
	ajax(url, request => {
		if (qs('.message', ajaxStatus(request.responseText))) { // success
			editChanged = null;
		}
	}, data, message);
	return false;
}

let stickyTimeout;

/** Display the response of an AJAX request in the status area
* @param {string} html
* @return {HTMLElement} the status area
*/
function ajaxStatus(html) {
	const ajaxstatus = qs('#ajaxstatus');
	setHtml('ajaxstatus', html);
	adminerHighlighter(qsa('code', ajaxstatus));
	messagesPrint(ajaxstatus);
	alterClass(ajaxstatus, 'sticky', html); // display the message also in a scrolled page but release it soon to not cover the content
	clearTimeout(stickyTimeout);
	stickyTimeout = setTimeout(() => alterClass(ajaxstatus, 'sticky'), 5000);
	return ajaxstatus;
}

/** Check if PHP can handle the uploaded files
* @param {number} count
* @param {string} countMessage
* @param {number} size
* @param {string} sizeMessage
* @this HTMLInputElement
*/
function fileChange(count, countMessage, size, sizeMessage) {
	if (this.files.length > count) {
		alert(countMessage);
	} else if (Array.from(this.files).reduce((sum, file) => sum + file.size, 0) > size) {
		alert(sizeMessage);
	}
}

/** Display the progress of the file upload
* @param {string} url
* @param {string} assign cookie identifying the session where PHP stores the progress - it uses the session name from php.ini which differs from Adminer's
* @this HTMLFormElement
*/
function uploadProgress(url, assign) {
	const progress = qs('progress', this);
	// the form can be submitted also without a file - by Run file in Import or by Save, Delete and Export in Select
	if (!progress || !Array.from(qsa('input[type=file]', this)).some(input => input.value)) {
		return;
	}
	cookie(assign, 1);
	let started = false;
	const poll = () => ajax(url, request => {
		const data = JSON.parse(request.responseText);
		if (!data.length) {
			if (started) {
				progress.value = 1;
				progress.textContent = '100%';
				cookie(assign, -1); // the session is no longer needed, don't keep its name taken
				return;
			}
		} else if (data[1]) {
			started = true;
			alterClass(progress, 'hidden');
			progress.value = data[0] / data[1];
			progress.textContent = Math.floor(100 * data[0] / data[1]) + '%';
		}
		setTimeout(poll, 1000);
	}, null, null); // null message - a failed poll would print an error and stop the polling
	setTimeout(poll, 1000);
}



/** Display edit field
* @param {MouseEvent} event
* @param {number} text display textarea instead of input, 2 - load long text
* @param {string} [warning] warning to display
* @return {boolean}
* @this HTMLElement
*/
function selectClick(event, text, warning) {
	const td = this;
	const target = event.target;
	if (!isCtrl(event) || (td.firstElementChild && td.firstElementChild.matches('input, textarea')) || target.matches('a')) {
		return;
	}
	const form = td.closest('form');
	// the same row can be displayed more than once in the result of the SQL command so the cells have no unique ID there
	const name = td.dataset.name || td.id;
	if (warning) {
		alert(warning);
		return true;
	}
	const original = td.innerHTML;
	text = text || /\n/.test(original);
	const input = ce(text ? 'textarea' : 'input', {
		onkeydown: event => {
			if (isEscape(event)) {
				inputBlur();
				td.innerHTML = original;
			}
		},
	});

	const pos = getSelection().anchorOffset;
	let value = td.dataset.value;
	if (value === undefined) {
		value = (td.firstChild && td.firstChild.alt) || td.textContent;
	}
	const tdStyle = window.getComputedStyle(td, null);

	input.style.width = Math.max(td.clientWidth - parseFloat(tdStyle.paddingLeft) - parseFloat(tdStyle.paddingRight), (text ? 200 : 20)) + 'px';

	if (text) {
		input.rows = value.split('\n').length;
	}
	td.innerHTML = '';
	td.append(input);
	const save = (form && form['save']) || qs('#save'); // each result of the SQL command has its own button
	if (save) { // missing if a plugin returns false from selectCommandPrint()
		save.disabled = false;
	}
	setupSubmitHighlight(td);
	input.focus();
	if (text == 2) { // long text
		return ajax(location.href + '&' + urlEscape(name) + '=', request => {
			if (request.responseText) {
				input.value = request.responseText;
				input.name = name;
			}
		});
	}
	input.value = value;
	input.name = name;
	input.selectionStart = pos;
	input.selectionEnd = pos;
	return true;
}



/** Load and display next page in select
* @param {string} loading
* @return {boolean} false
* @this HTMLLinkElement
*/
function selectLoadMore(loading) {
	const a = this;
	const title = a.innerHTML;
	const href = a.href;
	if (href) {
		ajax(href, request => {
			const tbody = ce('tbody');
			tbody.innerHTML = request.responseText;
			adminerHighlighter(qsa('code', tbody));
			qs('#table').tBodies[0].append(...tbody.children); // keep the rows in the original TBODY to continue the .odds highlighting
			const next = request.getResponseHeader('X-Next-Page'); // the response contains only the rows so the URL of the following page is sent in a header
			if (next) {
				a.href = next;
				a.innerHTML = title;
			} else {
				a.remove();
			}
		});
		a.innerHTML = loading;
		a.removeAttribute('href');
		return false;
	}
}



/** Setup highlighting of default submit button on form field focus
* @param {HTMLElement} parent
*/
function setupSubmitHighlight(parent) {
	qsa('input, select, textarea', parent).forEach(setupSubmitHighlightInput);
}

/** Setup submit highlighting for single element
* @param {HTMLElement} input
*/
function setupSubmitHighlightInput(input) {
	if (!/submit|button|image|file/.test(input.type)) {
		addEvent(input, 'focus', inputFocus);
		addEvent(input, 'blur', inputBlur);
		if (document.activeElement === input) {
			fire(input, 'focus'); // focus event was missed, e.g. jush.textarea() focuses the <pre> before this
		}
	}
}

/** Highlight default submit button
* @this HTMLInputElement
*/
function inputFocus() {
	inputBlur();
	alterClass(findDefaultSubmit(this), 'default', true);
}

/** Unhighlight default submit button */
function inputBlur() {
	alterClass(qs('input.default'), 'default'); // not findDefaultSubmit() - some buttons could get disabled since the highlighting
}

/** Find submit button used by Enter
* @param {HTMLElement} el
* @return {?HTMLInputElement}
*/
function findDefaultSubmit(el) {
	if (el.jushTextarea) {
		el = el.jushTextarea;
	}
	return (el.form ? qs('input[type=submit]:not([hidden]):enabled', el.form) : null); // the browser also skips disabled buttons in the implicit submission
}



/** Add event listener
* @param {HTMLElement} el
* @param {string} action without 'on'
* @param {function} handler
*/
function addEvent(el, action, handler) {
	el.addEventListener(action, handler);
}

/** Dispatch an event to run the handlers registered by the data attributes
* @param {?HTMLElement} el
* @param {string} type event name without 'on'
* @return {boolean} false if a handler has canceled the event
*/
function fire(el, type) {
	// bubbles - the handlers are delegated from the document; cancelable - the caller can skip the default action
	return (el ? el.dispatchEvent(new Event(type, {bubbles: true, cancelable: true})) : true);
}

/** Clone node and setup submit highlighting
* @param {HTMLElement} el
* @return {HTMLElement}
*/
function cloneNode(el) {
	const el2 = el.cloneNode(true);
	const selector = 'input, select, button';
	const origEls = qsa(selector, el);
	const cloneEls = qsa(selector, el2);
	for (const [i, origEl] of origEls.entries()) {
		for (const key in origEl) {
			if (/^on/.test(key) && origEl[key]) {
				cloneEls[i][key] = origEl[key];
			}
		}
	}
	setupSubmitHighlight(el2);
	return el2;
}

// documentElement - this file is loaded in <head> where document.body doesn't exist yet
alterClass(document.documentElement, 'js', true);
alterClass(document.documentElement, 'nojs'); // two calls, not classList.replace() - unsupported in Chrome < 61
alterClass(document.documentElement, 'insecure', !navigator.clipboard); // clipboard is undefined also in an insecure context
if (isMac) {
	addEventListener('DOMContentLoaded', macShortcuts);
}

mixin(document, {
	onchange: delegateEvent,
	onclick: bodyClick, // calls delegateEvent() itself
	ondblclick: delegateEvent,
	oninput: bodyInput, // calls delegateEvent() itself
	onkeydown: bodyKeydown, // calls delegateEvent() itself
	onmousedown: delegateEvent,
	onmouseout: delegateEvent,
	onmouseover: delegateEvent,
	onsearch: delegateEvent, // WebKit only, it bubbles
	onsubmit: bodySubmit, // calls delegateEvent() itself
});

'use strict'; // Adminer specific functions

// eslint-disable-next-line no-unassigned-vars
let autocompleter; // set in adminer.inc.php

/** Load syntax highlighting
* @param {string} version version of the database system as used in its documentation URLs
* @param {string} [vendor]
*/
function syntaxHighlighting(version, vendor) {
	addEventListener('DOMContentLoaded', () => {
		if (window.jush) {
			jush.create_links = 'target="_blank" rel="noreferrer noopener"';
			if (version) {
				for (let key in jush.urls) {
					let obj = jush.urls;
					if (typeof obj[key] != 'string') {
						obj = obj[key];
						key = 0;
					}
					// MariaDB page keys are resolved by jush itself from the 'mysql-key maria-key' entries
					obj[key] = (vendor == 'maria' ? obj[key].replace('dev.mysql.com/doc/mysql', 'mariadb.com/kb') : obj[key]) // MariaDB
						.replace('/doc/mysql', '/doc/refman/' + version) // MySQL
					;
					if (vendor != 'cockroach') {
						obj[key] = obj[key].replace('/docs/current', '/docs/' + version); // PostgreSQL
					} else if (obj == jush.urls.pgsql) { // jush.link_key.pgsql resolves the CockroachDB pages, the variables and extensions stay at PostgreSQL
						obj[key] = 'https://docs.cockroachlabs.com/docs/v' + version + '/$key';
					}
					if (/^\d\d$/.test(version)) { // the version of another system would make up a directory Oracle doesn't have
						obj[key] = obj[key].replace('/oracle-database/19/', '/oracle-database/' + version + '/');
					}
				}
			}
			if (window.jushLinks) {
				jush.custom_links = jushLinks;
			}
			jush.highlight_tag('code', 0);
			adminerHighlighter = els => jush.highlight_tag(els, 0);
			for (const tag of qsa('textarea')) {
				if (/(^|\s)jush-/.test(tag.className)) {
					const pre = jush.textarea(tag, autocompleter);
					if (pre) {
						tag.jushPre = pre;
						if (tag.dataset.errorLine) {
							jush.mark_line(pre, +tag.dataset.errorLine); // jush.textarea() removes the mark on typing
						}
						setupSubmitHighlightInput(pre);
						tag.onchange = () => {
							pre.textContent = tag.value;
							pre.oninput();
						};
					}
				}
			}
		}
	});
}

/** Try to change input type to password or to text
* @param {HTMLInputElement} el
* @param {boolean} [disable]
*/
function typePassword(el, disable) {
	try {
		el.type = (disable ? 'text' : 'password');
	} catch (e) { // empty
	}
}

/** Display the password as a text if it's hashed
* @this HTMLInputElement
*/
function hashedClick() {
	typePassword(this.form['pass'], this.checked);
}

/** Uncheck the All privileges checkbox after granting a single privilege
* @param {string} id
* @this HTMLInputElement
*/
function grantsClick(id) {
	if (this.checked) {
		formUncheck(id);
	}
}

/** Install handlers in messages
* @param {HTMLElement} [parent]
*/
function messagesPrint(parent) {
	copyCode(parent);
}

/** Copy code to clipboard
* @param {HTMLElement} [parent]
*/
function copyCode(parent) {
	for (const el of qsa('.icon-copy', parent)) {
		el.onclick = () => {
			const code = qs('code', el.parentElement);
			navigator.clipboard.writeText(code.dataset.full || code.innerText).then(() => {
				alterClass(el, 'copied', true);
				setTimeout(() => alterClass(el, 'copied'), 1000);
			}, () => {});
			return false;
		};
	}
}



/** Hide or show some login rows for selected driver
* @this HTMLSelectElement
*/
function loginDriver() {
	const trs = this.closest('table').rows;
	const disabled = /sqlite|igdb/.test(selectValue(this));
	alterClass(trs[1], 'hidden', disabled);	// 1 - row with server
	qs('input', trs[1]).disabled = disabled;
}



let dbCtrl;
const dbPrevious = {};

/** Check if database should be opened to a new window
* @param {MouseEvent} event
* @this HTMLSelectElement
*/
function dbMouseDown(event) {
	// Firefox: mouse-down event does not contain pressed key information for OPTION.
	// Chrome: mouse-down event has inherited key information from SELECT.
	// So we ignore the event for OPTION to work Ctrl+click correctly everywhere.
	if (event.target.tagName == 'OPTION') {
		return;
	}

	dbCtrl = isCtrl(event);
	if (dbPrevious[this.name] == undefined) {
		dbPrevious[this.name] = this.value;
	}
}

/** Load database after selecting it
* @this HTMLSelectElement
*/
function dbChange() {
	if (dbCtrl) {
		this.form.target = '_blank';
	}
	this.form.submit();
	this.form.target = '';
	if (dbCtrl && dbPrevious[this.name] != undefined) {
		this.value = dbPrevious[this.name];
		dbPrevious[this.name] = undefined;
	}
}



/** Check whether the query will be executed with index
* @this HTMLElement
*/
function selectFieldChange() {
	const form = this.form;
	const ok = (() => {
		if ([...qsa('input', form)].some(input => input.value && /^fulltext/.test(input.name))) {
			return true;
		}
		let ok = form.limit.value;
		let group = false;
		const columns = {};
		for (const select of qsa('select, input', form)) { // input - a column typed by a plugin
			const col = selectValue(select);
			let match = /^(where.+)col]/.exec(select.name);
			if (match) {
				const op = selectValue(form[match[1] + 'op]']);
				const val = form[match[1] + 'val]'].value;
				if (col in indexColumns && (!/LIKE|REGEXP/.test(op) || (op == 'LIKE' && val[0] != '%'))) {
					return true;
				} else if (col || val) {
					ok = false;
				}
			}
			if ((match = /^(columns.+)fun]/.exec(select.name))) {
				if (/^(avg|count|count distinct|group_concat|max|min|sum)$/.test(col)) {
					group = true;
				}
				const val = selectValue(form[match[1] + 'col]']);
				if (val) {
					columns[col && col != 'count' ? '' : val] = 1;
				}
			}
			if (col && /^order/.test(select.name)) {
				if (!(col in indexColumns)) {
					ok = false;
				}
				break;
			}
		}
		if (group) {
			for (const col in columns) {
				if (!(col in indexColumns)) {
					ok = false;
				}
			}
		}
		return ok;
	})();
	setHtml('noindex', (ok ? '' : '!'));
}

/** Close the help and add a row in select fieldset after selecting a function
* @this HTMLSelectElement
*/
function selectFunAddRow() {
	helpClose();
	fire(qsl('select, input', this.parentNode), 'change'); // qsl - the column is printed after the function
}



let added = '.';

/** Check if val is equal to a-delimiter-b where delimiter is '_', '' or big letter
* @param {string} val
* @param {string} a
* @param {string} b
* @return {boolean}
*/
function delimiterEqual(val, a, b) {
	return (val == a + '_' + b || val == a + b || val == a + b[0].toUpperCase() + b.slice(1));
}

/** Escape string to use as identifier
* @param {string} s
* @return {string}
*/
function idfEscape(s) {
	return s.replace(/`/, '``');
}



/** Set up event handlers for edit_fields(). */
function editFields() {
	for (const el of qsa('[name$="[field]"]')) {
		el.oninput = function () {
			editingNameChange.call(this);
			if (!this.defaultValue) {
				editingAddRow.call(this);
			}
		};
	}
	for (const el of qsa('[name$="[length]"]')) {
		mixin(el, {onfocus: editingLengthFocus, oninput: editingLengthChange});
	}
	for (const el of qsa('[name$="[type]"]')) {
		mixin(el, {
			onfocus: function () {
				lastType = selectValue(this);
			},
			onchange: editingTypeChange,
		});
	}
	const table = qs('#edit-fields');
	let dragged;
	const dragStart = el => {
		dragged = el.closest('tr');
		alterClass(table, 'dragging', true);
		alterClass(dragged, 'dragged', true);
	};
	const dragMove = el => { // returns whether the element is a valid drop target
		const row = el && el.closest('tr'); // el is null if the touch is outside the viewport
		if (!dragged || !row || !qs('[draggable]', row)) {
			return false;
		}
		if (row != dragged) {
			const rows = [...row.parentNode.children];
			row.parentNode.insertBefore(dragged, (rows.indexOf(row) < rows.indexOf(dragged) ? row : row.nextSibling));
		}
		return true;
	};
	const dragEnd = () => {
		alterClass(dragged, 'dragged');
		dragged = null;
		alterClass(table, 'dragging');
	};
	mixin(table, {
		ondragstart: e => {
			if (e.target.draggable) {
				e.dataTransfer.effectAllowed = 'move';
				dragStart(e.target);
			}
		},
		ondragend: dragEnd,
		ondragover: e => {
			if (dragMove(e.target)) {
				e.preventDefault();
			}
		},
	});
	// HTML5 drag and drop doesn't work on touch screens; addEventListener() because the handlers must not be passive
	table.addEventListener('touchstart', e => {
		const el = e.target.closest('button');
		if (el && el.draggable) {
			e.preventDefault(); // to not scroll the page
			dragStart(el);
		}
	}, {passive: false});
	table.addEventListener('touchmove', e => {
		if (dragged) {
			e.preventDefault();
			const touch = e.touches[0];
			dragMove(document.elementFromPoint(touch.clientX, touch.clientY));
		}
	}, {passive: false});
	table.addEventListener('touchend', dragEnd);
}

/** Handle clicks on fields editing
* @param {MouseEvent} event
* @return {boolean} false to cancel action
*/
function editingClick(event) {
	let el = event.target.closest('button');
	if (el) {
		const name = el.name;
		if (/^add\[/.test(name)) {
			editingAddRow.call(el, 1);
		} else if (/^drop_col\[/.test(name)) {
			editingRemoveRow.call(el, 'fields$1[field]');
		}
		return false;
	}
	el = event.target;
	if (!el.matches('input')) {
		el = el.closest('label');
		el = el && qs('input', el);
	}
	if (el) {
		if (el.name == 'auto_increment_col') {
			const field = el.form['fields[' + el.value + '][field]'];
			if (!field.value) {
				field.value = 'id';
				field.oninput();
			}
		}
	}
}

/** Handle input on fields editing
* @param {InputEvent} event
*/
function editingInput(event) {
	const el = event.target;
	if (/\[default]$/.test(el.name)) {
		el.previousElementSibling.checked = true;
		el.previousElementSibling.selectedIndex = Math.max(el.previousElementSibling.selectedIndex, 1);
	}
}

/** Detect foreign key
* @this HTMLInputElement
*/
function editingNameChange() {
	const name = this.name.slice(0, -7);
	const type = this.form[name + '[type]'];
	const opts = type.options;
	let candidate; // don't select anything with ambiguous match (like column `id`)
	const val = this.value;
	for (let i = opts.length; i--;) {
		const match = /(.+)`(.+)/.exec(opts[i].value);
		if (!match) { // common type
			if (candidate && i == opts.length - 2 && val == opts[candidate].value.replace(/.+`/, '') && name == 'fields[1]') { // single target table, link to column, first field - probably `id`
				return;
			}
			break;
		}
		const [, base, column] = match;
		for (const table of [base, base.replace(/s$/, ''), base.replace(/es$/, '')]) {
			if (val == column || val == table || delimiterEqual(val, table, column) || delimiterEqual(val, column, table)) {
				if (candidate) {
					return;
				}
				candidate = i;
				break;
			}
		}
	}
	if (candidate) {
		type.selectedIndex = candidate;
		type.onchange();
	}
}

/** Add table row for next field
* @param {number} [focus]
* @return {boolean} false
* @this HTMLInputElement
*/
function editingAddRow(focus) {
	const match = /(\d+)(\.\d+)?/.exec(this.name);
	const x = match[0] + (match[2] ? added.slice(match[2].length) : added) + '1';
	const row = this.closest('tr');
	const row2 = cloneNode(row);
	let tags = qsa('select, input, button', row);
	let tags2 = qsa('select, input, button', row2);
	for (const [i, tag] of tags.entries()) {
		tags2[i].name = tag.name.replace(/[0-9.]+/, x);
		tags2[i].selectedIndex = (/\[(generated)/.test(tag.name) ? 0 : tag.selectedIndex);
	}
	tags = qsa('input', row);
	tags2 = qsa('input', row2);
	for (const [i, tag] of tags.entries()) {
		if (tag.name == 'auto_increment_col') {
			tags2[i].value = x;
			tags2[i].checked = false;
		}
		if (/\[(orig|field|comment|default)/.test(tag.name)) {
			tags2[i].value = '';
		}
		if (/\[(generated)/.test(tag.name)) {
			tags2[i].checked = false;
		}
	}
	tags[0].oninput = editingNameChange;
	row.parentNode.insertBefore(row2, row.nextSibling);
	if (focus) {
		tags2[0].oninput = editingNameChange;
		tags2[0].focus();
	}
	added += '0';
	maxFieldsCheck();
	return false;
}

/** Display the error about the number of fields if the form has too many columns */
function maxFieldsCheck() {
	const el = qs('#max-fields'); // only in create.inc.php, only if max_input_vars is set and only if the message is hidden
	// [orig] is printed for every column and editingRemoveRow() keeps it, so the removed columns are counted too
	if (el && qsa('#edit-fields [name$="[orig]"]').length > +el.dataset.columns) {
		alterClass(el, 'hidden');
		qs('#edit-fields').parentNode.after(el); // the top of the page is not visible after adding columns
	}
}

/** Add table row after the last field; used by drivers where columns can be added only to the end
* @return {boolean} false for success
* @this HTMLElement
*/
function editingAddLastRow() {
	const inputs = qsa('#edit-fields [name$="[field]"]');
	if (!inputs.length) {
		return true; // submit the form to add the row by PHP
	}
	return editingAddRow.call(inputs[inputs.length - 1], 1);
}

/** Remove table row for field
* @param {string} name regular expression replacement
* @return {boolean} false
* @this HTMLInputElement
*/
function editingRemoveRow(name) {
	const field = this.form[this.name.replace(/[^[]+(.+)/, name)];
	field.remove();
	this.closest('tr').hidden = true;
	return false;
}

let lastType = '';

/** Clear length and hide collation or unsigned
* @this HTMLSelectElement
*/
function editingTypeChange() {
	const type = this;
	const name = type.name.slice(0, -6);
	const text = selectValue(type);
	for (const el of type.form.elements) {
		if (el.name == name + '[length]') {
			if (!(
				(/(char|binary)$/.test(lastType) && /(char|binary)$/.test(text))
				|| (/(enum|set)$/.test(lastType) && /(enum|set)$/.test(text))
			)) {
				el.value = '';
			}
			el.oninput.call(el);
		}
		if (lastType == 'timestamp' && el.name == name + '[generated]' && /timestamp/i.test(type.form[name + '[default]'].value)) {
			el.checked = false;
			el.selectedIndex = 0;
		}
		// the expressions come from option_types(), the options of the other columns start with another name
		if (el.dataset.types && el.name.startsWith(name + '[')) {
			alterClass(el, 'hidden', !new RegExp(el.dataset.types).test(text));
		}
	}
	helpClose();
}

/** Mark length as required
* @this HTMLInputElement
*/
function editingLengthChange() {
	alterClass(this, 'required', !this.value.length && /var(char|binary)$/.test(selectValue(this.parentNode.previousSibling.firstChild)));
}

/** Edit enum or set
* @this HTMLInputElement
*/
function editingLengthFocus() {
	const td = this.parentNode;
	if (/^(enum|set)$/.test(selectValue(td.previousSibling.firstChild))) {
		const edit = qs('#enum-edit');
		edit.value = enumValues(this.value);
		td.append(edit);
		this.hidden = true;
		edit.hidden = false;
		edit.focus();
	}
}

/** Get enum values
* @param {string} s
* @return {string} values separated by newlines
*/
function enumValues(s) {
	const re = /(^|,)\s*'(([^\\']|\\.|'')*)'\s*/g;
	const result = [];
	let offset = 0;
	let match;
	while ((match = re.exec(s))) {
		if (offset != match.index) {
			break;
		}
		result.push(match[2].replace(/'(')|\\(.)/g, '$1$2'));
		offset += match[0].length;
	}
	return (offset == s.length ? result.join('\n') : s);
}

/** Finish editing of enum or set
* @this HTMLTextAreaElement
*/
function editingLengthBlur() {
	const field = this.parentNode.firstChild;
	const val = this.value;
	field.value = (/^'[^\n]+'$/.test(val) ? val : val && "'" + val.replace(/\n+$/, '').replace(/'/g, "''").replace(/\\/g, '\\\\').replace(/\n/g, "','") + "'");
	field.hidden = false;
	this.hidden = true;
}

/** Show or hide selected table column
* @param {boolean} checked
* @param {number} column
*/
function columnShow(checked, column) {
	for (const tr of qsa('tr', qs('#edit-fields'))) {
		alterClass(tr.cells[column], 'hidden', !checked);
	}
}

/** Show or hide the clicked table column
* @param {number} column
* @this HTMLInputElement
*/
function columnShowClick(column) {
	columnShow(this.checked, column);
}

/** Show or hide index column options
* @this HTMLInputElement
*/
function indexOptionsShow() {
	for (const option of qsa('.idxopts')) {
		alterClass(option, 'hidden', !this.checked);
	}
}

/** Reload the page for the selected target
* @this HTMLSelectElement
*/
function foreignChange() {
	const form = this.form;
	form['change-js'].value = '1';
	form.submit();
}

/** Display partition options
* @this HTMLSelectElement
*/
function partitionByChange() {
	const partitionTable = /RANGE|LIST/.test(selectValue(this));
	alterClass(this.form['partitions'], 'hidden', partitionTable || !this.selectedIndex);
	alterClass(qs('#partition-table'), 'hidden', !partitionTable);
	helpClose();
}

/** Add next partition row
* @this HTMLInputElement
*/
function partitionNameChange() {
	const tr = this.closest('tr');
	const row = cloneNode(tr);
	row.firstChild.firstChild.value = '';
	tr.parentNode.append(row);
	this.removeAttribute('data-oninput'); // the appended row adds the next one
}

/** Show or hide comment fields
* @param {boolean} [focus] whether to focus Comment if checked
* @this HTMLInputElement
*/
function editingCommentsClick(focus) {
	const comment = this.form['Comment'];
	columnShow(this.checked, 7);
	alterClass(comment, 'hidden', !this.checked);
	if (focus && this.checked) {
		comment.focus();
	}
}



/** Uncheck 'all' checkbox
* @param {MouseEvent} event
* @this HTMLTableElement
*/
function dumpClick(event) {
	let el = event.target.closest('label');
	if (el) {
		el = qs('input', el);
		const match = /(.+)\[]$/.exec(el.name);
		if (match) {
			checkboxClick.call(el, event);
			formUncheck('check-' + match[1]);
		}
	}
}



/** Add row for foreign key
* @this HTMLSelectElement
*/
function foreignAddRow() {
	const tr = this.closest('tr');
	const row = cloneNode(tr); // the clone keeps the attribute so that it adds the next row
	this.removeAttribute('data-onchange');
	for (const select of qsa('select', row)) {
		select.name = select.name.replace(/\d+]/, '1$&');
		select.selectedIndex = 0;
	}
	tr.parentNode.append(row);
}

/** Name the foreign key by its source columns
* @param {string} nameRe matching the names created from the pattern
* @param {string} namePattern Adminer::namePattern() with the table
* @this HTMLTableElement
*/
function foreignName(nameRe, namePattern) {
	const name = this.closest('form')['constraint'];
	if (name && new RegExp(nameRe).test(name.value)) { // don't overwrite a name typed by the user
		const columns = [...qsa('select', this)].filter(select => /^source/.test(select.name)).map(select => selectValue(select)).filter(value => value);
		name.value = namePattern.replace('{columns}', () => columns.join('_')); // function - column names can contain $&
	}
}



/** Add row for indexes
* @this HTMLSelectElement
*/
function indexesAddRow() {
	const tr = this.closest('tr');
	const row = cloneNode(tr);
	for (const tag of qsa('select, input, button', row)) {
		tag.name = tag.name.replace(/\[\d+/, '$&1'); // indexes[$j] and drop_col[$j]
		if (tag.matches('select')) {
			tag.selectedIndex = 0;
		} else if (tag.matches('input')) {
			tag.value = '';
		}
	}
	tr.parentNode.append(row);
}

/** Get the name of an index from its columns
* @param {HTMLTableRowElement} tr
* @param {string} pattern Adminer::namePattern() with the table
* @return {string}
*/
function indexesName(tr, pattern) {
	const names = [...qsa('select, input', tr)].filter(column => /\[columns]/.test(column.name)).map(column => selectValue(column)).filter(value => value);
	return pattern.replace('{columns}', () => names.join('_')); // function - column names can contain $&
}

/** Change index type, the last row also adds the next one
* @param {Object<string, string>} namePatterns type => Adminer::namePattern() with the table
* @this HTMLSelectElement
*/
function indexesChangeType(namePatterns) {
	const tr = this.closest('tr');
	if (!tr.nextElementSibling) {
		indexesAddRow.call(this);
	}
	const name = this.form[this.name.replace(/].*/, '][name]')];
	const pattern = namePatterns[selectValue(this)];
	if (pattern !== undefined && Object.keys(namePatterns).some(type => name.value == indexesName(tr, namePatterns[type]))) { // don't overwrite a name typed by the user
		name.value = indexesName(tr, pattern);
	}
}

/** Change column in index, the last column also adds the next one
* @param {Object<string, string>} namePatterns type => Adminer::namePattern() with the table
* @this HTMLSelectElement
*/
function indexesChangeColumn(namePatterns) {
	const field = this;
	const td = field.closest('td');
	const columns = [...qsa('select, input', td)].filter(column => /\[columns]/.test(column.name));
	const type = field.form[field.name.replace(/].*/, '][type]')];
	if (columns[columns.length - 1] == field) { // the appended column becomes the last one so it adds the next
		if (!type.selectedIndex) {
			while (selectValue(type) != 'INDEX' && type.selectedIndex < type.options.length) {
				type.selectedIndex++;
			}
			fire(type, 'change');
		}
		const column = cloneNode(field.parentNode);
		for (const select of qsa('select', column)) {
			select.name = select.name.replace(/]\[\d+/, '$&1');
			select.selectedIndex = 0;
		}
		for (const input of qsa('input', column)) {
			input.name = input.name.replace(/]\[\d+/, '$&1');
			if (input.type != 'checkbox') {
				input.value = '';
			}
		}
		td.append(column);
	}
	field.form[field.name.replace(/].*/, '][name]')].value = indexesName(field.closest('tr'), namePatterns[selectValue(type)] || '');
}



/** Update the form action
* @param {string} root
* @param {number} maxLength
* @this HTMLFormElement
*/
function sqlSubmit(root, maxLength) {
	const suffix = (this['limit'].value ? '&limit=' + +this['limit'].value : '')
		+ (this['error_stops'].checked ? '&error_stops=1' : '')
		+ (this['only_errors'].checked ? '&only_errors=1' : '');
	const action = root + '&sql=' + urlEscape(this['query'].value) + suffix;
	this.action = ((location.origin + location.pathname + action).length < maxLength
		? action
		: root + '&sql=' + suffix
	);
}

/** Export the result table by JS without re-running the query
* @param {MouseEvent} event
* @return {boolean} false when handled by JS
* @this HTMLInputElement
*/
function sqlExport(event) {
	const form = this.form;
	const format = form['format'].value;
	const output = form['output'].value;
	if (!/^(csv|csv;|tsv)$/.test(format) || !/^(text|file)$/.test(output)) {
		return true;
	}
	const table = qs('.scrollable table', form);
	if (!table) {
		return true;
	}
	// <i> other than NULL means the value is not displayed fully
	if ([...qsa('i', table)].some(i => i.textContent != 'NULL')) {
		return true;
	}
	// save_settings() - the server-side export stores the settings too
	cookie(
		'adminer_import=' + encodeURIComponent('output=' + output + '&format=' + encodeURIComponent(format)),
		30 // 30 - $lifetime of cookie()
	);
	const tsv = (format == 'tsv');
	const quotable = new RegExp('["\n]|^0[^.]|\\.\\d*0$|' + (tsv ? '\t' : '[,;]|^$')); // dump_csv()
	let data = '\ufeff'; // UTF-8 byte order mark
	for (const row of qsa('tr', table)) {
		data += Array.from(row.children).map(cell => {
			const val = (qsa('i', cell).length ? '' : cell.textContent); // <i> - NULL
			return (quotable.test(val) ? '"' + val.replace(/"/g, '""') + '"' : val);
		}).join(format == 'csv' ? ',' : (tsv ? '\t' : ';')) + '\r\n';
	}
	const url = URL.createObjectURL(new Blob([data], {type: (output == 'file' ? 'text/csv' : 'text/plain') + '; charset=utf-8'}));
	if (output == 'file') {
		const a = ce('a', {href: url, download: 'sql.csv'});
		document.body.append(a);
		a.click();
		a.remove();
		setTimeout(() => URL.revokeObjectURL(url));
	} else if (isCtrl(event) || event.shiftKey) {
		// the same modifiers open the server-side export in a new window in bodyClick(), submit the form if the pop-up is blocked
		// the URL is not revoked to not break the load of the new window
		return !open(url);
	} else {
		location.href = url;
	}
	return false;
}

/** Save the values modified by Ctrl+click in the result of the SQL command
* @param {string} message
* @return {boolean} false
* @this HTMLInputElement submit button
*/
function sqlSave(message) {
	const button = this;
	const form = button.form;
	ajax(form.action, request => {
		const response = ce('div');
		response.innerHTML = request.responseText;
		for (const el of qsa('[data-name]', response)) {
			// a join can display the same row more than once, refresh all its cells
			for (const td of qsa('[data-name="' + el.dataset.name.replace(/[\\"]/g, '\\$&') + '"]')) {
				td.innerHTML = el.innerHTML;
			}
			el.remove();
		}
		button.disabled = !qs('.error', response); // an error can be fixed and saved again
		ajaxStatus(response.innerHTML);
	}, formData(form, button), message);
	return false;
}



/** Handle changing trigger time or event
* @param {string} nameRe string - a regular expression can't be passed in a data attribute
* @param {string} namePattern Adminer::namePattern() with the table
* @this HTMLSelectElement
*/
function triggerChange(nameRe, namePattern) {
	const form = this.form;
	const formEvent = selectValue(form['Event']);
	if (new RegExp(nameRe).test(form['Trigger'].value)) {
		form['Trigger'].value = namePattern
			.replace('{timing}', selectValue(form['Timing'])[0].toLowerCase())
			.replace('{event}', ['INSERT', 'UPDATE', 'DELETE'].filter(event => formEvent.includes(event)).map(event => event[0].toLowerCase()).join(''))
			.replace('{type}', (/ROW/.test(selectValue(form['Type'])) ? 'row' : 'statement')) // MS SQL AS and Oracle '' are statement-level
			.replace('{columns}', () => (/ OF/.test(formEvent) ? form['Of'].value.split(',').map(column => column.trim()).filter(column => column).map(column => '_' + column).join('') : '')) // function - column names can contain $&
		;
	}
	alterClass(form['Of'], 'hidden', !/ OF/.test(formEvent));
}



/** Highlight the routine definition by the selected language
* @param {Object} jushLangs routine language => syntax highlighting language
* @this HTMLSelectElement
*/
function routineLanguage(jushLangs) {
	const jushClass = 'jush-' + jushLangs[selectValue(this)];
	const textarea = this.form['definition'];
	textarea.className = textarea.className.replace(/jush-\S+/, jushClass);
	const pre = textarea.jushPre;
	if (pre) {
		pre.className = pre.className.replace(/jush-\S+/, jushClass);
		textarea.onchange(); // highlights the <pre> again, jush.textarea() reads the language from the <textarea>
	}
}



let that, x, y; // em, tablePos and tablePosDefault defined in schema.inc.php

/** Get mouse position
* @param {MouseEvent} event
* @this HTMLElement
*/
function schemaMousedown(event) {
	if (event.button == 0) { // 0 - left button
		that = this;
		x = event.clientX - this.offsetLeft;
		y = event.clientY - this.offsetTop;
	}
}

/** Connect one end of a reference to the vertical line
* @param {HTMLElement} div .references inside the table
* @param {number} line position of the vertical line in em
* @param {number} table position of the table in em
*/
function schemaRef(div, line, table) {
	const left = line - table;
	const inner = div.querySelector('div');
	if (left > 0) { // the line is right of the table so the reference leaves it on its right edge, 100% is the width of the table
		div.style.left = '100%';
		div.style.width = 'calc(' + left + 'em - 100%)';
		inner.style.width = '100%';
	} else {
		div.style.left = left + 'em';
		div.style.width = '';
		inner.style.width = -left + 'em';
	}
}

/** Move object
* @param {MouseEvent} event
*/
function schemaMousemove(event) {
	if (that !== undefined) {
		const left = (event.clientX - x) / em;
		const top = (event.clientY - y) / em;
		const lineSet = { };
		for (const div of qsa('div', that)) {
			if (div.classList.contains('references')) {
				const div2 = qs('[id="' + (/^refs/.test(div.id) ? 'refd' : 'refs') + div.id.slice(4) + '"]');
				if (!div2) { // table in another schema
					continue;
				}
				const ref = (tablePos[div.title] || tablePosDefault[div.title] || [div2.parentNode.offsetTop / em, div2.parentNode.offsetLeft / em]);
				const id = div.id.replace(/^ref.(.+)-.+/, '$1');
				let lineLeft = left - 1; // a self-reference, the line is left of the table
				if (div.parentNode != div2.parentNode) {
					const refs = /^refs/.test(div.id); // refs is in the referencing table, refd in the referenced one
					const source = (refs ? left : ref[1]);
					const target = (refs ? ref[1] : left);
					const width = (refs ? that : div2.parentNode).offsetWidth / em;
					// 1 em right of the referencing table, unless the referenced table is not right of it - then left of both
					lineLeft = (target - 1 > source + width ? source + width + 1 : Math.min(source, target) - 1);
					schemaRef(div, lineLeft, left);
					schemaRef(div2, lineLeft, ref[1]);
				}
				if (!lineSet[id]) {
					const line = qs('[id="' + div.id.replace(/^....(.+)-.+$/, 'refl$1') + '"]');
					const top1 = top + div.offsetTop / em;
					let top2 = top + div2.offsetTop / em;
					if (div.parentNode != div2.parentNode) {
						top2 += ref[0] - top;
						line.querySelector('div').style.height = Math.abs(top1 - top2) + 'em';
					}
					line.style.left = lineLeft + 'em';
					line.style.top = Math.min(top1, top2) + 'em';
					lineSet[id] = true;
				}
			}
		}
		that.style.left = left + 'em';
		that.style.top = top + 'em';
	}
}

/** Finish move
* @param {MouseEvent} event
* @param {string} db
*/
function schemaMouseup(event, db) {
	if (that !== undefined) {
		tablePos[that.firstChild.firstChild.firstChild.data] = [(event.clientY - y) / em, (event.clientX - x) / em];
		that = undefined;
		let s = '';
		for (const key in tablePos) {
			const [top, left] = tablePos[key];
			s += '_' + key + ':' + Math.round(top) + 'x' + Math.round(left);
		}
		s = s.slice(1);
		const link = qs('#schema-link');
		link.href = link.href.replace(/[^=]+$/, '') + urlEscape(s);
		// encodeURIComponent() instead of urlEscape() - it keeps ';' verbatim, which would end the cookie value
		cookie('adminer_schema-' + db + '=' + encodeURIComponent(s), 30); //! special chars in db
	}
}



let helpOpen, helpIgnore; // when mouse outs <option> then it mouse overs border of <select> - ignore it

/** Display help
* @param {string} text
* @param {number} side 1 to display on left side, 0 on top
* @param {MouseEvent} event
* @this HTMLElement
*/
function helpMouseover(text, side, event) {
	const target = event.target;
	if (!text) {
		helpClose();
	} else if (window.jush && (!helpIgnore || this != target)) {
		helpOpen = 1;
		const help = qs('#help');
		help.textContent = text;
		jush.highlight_tag([help]);
		alterClass(help, 'hidden');
		const rect = target.getBoundingClientRect();
		const body = document.documentElement;
		help.style.top = (body.scrollTop + rect.top - (side ? (help.offsetHeight - target.offsetHeight) / 2 : help.offsetHeight)) + 'px';
		help.style.left = (body.scrollLeft + rect.left - (side ? help.offsetWidth : (help.offsetWidth - target.offsetWidth) / 2)) + 'px';
	}
}

/** Display help with the hovered value
* @param {string} regexp regular expression transforming the value, empty to display the value itself
* @param {string} replacement can use $& and $1
* @param {MouseEvent} event
* @this HTMLSelectElement
*/
function helpValueMouseover(regexp, replacement, event) {
	// target - the hovered <option> in an open <select>
	const value = event.target.value;
	// new RegExp() - a regular expression can't be passed in a data attribute
	// 1 - an open <select> would cover the help displayed on top
	helpMouseover.call(this, (value && regexp ? value.replace(new RegExp(regexp), replacement) : value), 1, event);
}

/** Close help after timeout
* @param {MouseEvent} event
* @this HTMLElement
*/
function helpMouseout(event) {
	helpOpen = 0;
	helpIgnore = (this != event.target);
	setTimeout(() => {
		if (!helpOpen) {
			helpClose();
		}
	}, 200);
}

/** Keep the help open while hovering it */
function helpKeep() {
	helpOpen = 1;
}

/** Close help */
function helpClose() {
	alterClass(qs('#help'), 'hidden', true);
}

'use strict'; // the box searching tables, schemas, databases and the sections of the database and server pages, opened by Ctrl+K

let jumpColumn = +(/(?:^|; )adminer_jump=(\d)/.exec(document.cookie) || [0, 0])[1]; // the action chosen by Left and Right, kept when moving between rows and remembered for the next pages
const jumpSections = []; // [label, URL] of the sections and the links of the database and server pages, filled by Adminer::navigation() and plugins

/** Set up the box searching tables, schemas and databases */
function jumpSetup() {
	const input = qs('#jump input');
	input.placeholder += (isMac ? ' (⌘K)' : ' (Ctrl+K)');
	input.onfocus = jumpInput;
	input.onblur = () => {
		jumpToggle(false);
		alterClass(qs('#jump'), 'fixed');
	};
}

/** Focus the search box by Ctrl+K
* @param {KeyboardEvent} event
* @return {boolean|undefined} false if the box was focused
*/
function jumpShortcut(event) {
	const input = qs('#jump input');
	if (input && isCtrl(event) && !event.shiftKey && event.key.toLowerCase() == 'k') {
		const box = input.parentNode;
		alterClass(box, 'fixed', box.getBoundingClientRect().bottom < 0); // show it at the top of the scrolled page instead of scrolling up
		input.focus();
		input.select();
		return false;
	}
}

/** Get the URL of a page with the parameters of the search form
* @param {string|string[]} params appended to the URL, e.g. 'query=x'
* @param {boolean} [keepDb] keep db and ns
* @return {string}
*/
function jumpUrl(params, keepDb) {
	const parts = [];
	for (const field of qsa('input', qs('#jump-form'))) {
		if (keepDb || !/^(db|ns)$/.test(field.name)) {
			parts.push(urlEscape(field.name) + '=' + urlEscape(field.value));
		}
	}
	return '?' + parts.concat(params).join('&');
}

/** Get the items to search: tables from the menu, schemas and databases from their selects
* @return {{tables: JumpItem[], others: JumpItem[]}}
* @typedef {{name: string, links: Array<string[]>, kind?: string, search?: boolean}} JumpItem links are [href, label], the first one without a label opens the name
*/
function jumpItems() {
	const input = qs('#jump input');
	const tables = [];
	for (const li of qsa('#tables li')) { // also the items hidden by the plugin tables-filter
		const select = qs('a.select', li);
		const main = qs('a.structure, a.view, span', li);
		if (select) {
			const href = select.getAttribute('href');
			const links = (main && main.matches('a') ? [[main.getAttribute('href')], [href, input.dataset.select]] : [[href]]);
			if (!main || !main.matches('.view')) {
				links.push([href.replace('&select=', '&edit='), input.dataset.insert]);
			}
			tables.push({name: (main || select).textContent.trim(), links}); // textContent - the plugin tables-filter wraps the matches in <strong>
		}
	}
	const db = qs('#jump-form [name="db"]');
	const others = [];
	for (const option of qsa('#dbs select[name="ns"] option')) {
		if (option.value != '' && !option.selected) {
			others.push({name: option.value, links: [[jumpUrl(['db=' + urlEscape(db.value), 'ns=' + urlEscape(option.value)])]], kind: input.dataset.schema});
		}
	}
	for (const option of qsa('#dbs select[name="db"] option')) {
		if (option.value != '' && !option.selected) {
			others.push({name: option.value, links: [[jumpUrl('db=' + urlEscape(option.value))]], kind: input.dataset.database});
		}
	}
	return {tables, others};
}

/** Print the items matching the search box
* @this HTMLInputElement
*/
function jumpInput() {
	const query = this.value; // not trim() - a leading space finds the second word, e.g. " view" in "Create view"
	if (query == '') {
		jumpToggle(false);
		return;
	}
	const lower = query.toLowerCase();
	const filter = items => {
		const starts = items.filter(item => item.name.toLowerCase().startsWith(lower));
		return starts.concat(items.filter(item => item.name.toLowerCase().indexOf(lower) > 0));
	};
	const items = jumpItems();
	const tables = filter(items.tables);
	const sections = filter(jumpSections.map(([name, url]) => ({name, links: [[url]]})));
	const others = filter(items.others);
	if (items.tables.length && this.dataset.search) { // also if no table name matches
		sections.push({name: query, links: [[jumpUrl('query=' + urlEscape(query), true), this.dataset.search]], search: true});
	}
	let list = qs('#jump-list');
	if (!list) {
		list = ce('ul', {
			id: 'jump-list',
			role: 'listbox',
			onmousedown: event => event.preventDefault(), // keep the focus in the box so that the click follows the link
			onmouseover: event => {
				const a = event.target.closest('a');
				if (a) {
					const li = a.closest('li');
					jumpSelect([...li.parentNode.children].indexOf(li), [...qsa('a', li)].indexOf(a));
				}
			},
		});
		this.parentNode.appendChild(list);
	}
	const highlight = name => {
		const parts = [];
		let start = 0;
		for (let pos; (pos = name.toLowerCase().indexOf(lower, start)) != -1; start = pos + lower.length) {
			parts.push(name.slice(start, pos), ce('strong', {}, name.substr(pos, lower.length)));
		}
		parts.push(name.slice(start));
		return parts;
	};
	list.textContent = '';
	for (const item of tables.concat(sections, others)) {
		const li = ce('li', {id: 'jump-' + list.children.length, role: 'option'});
		for (const [href, label] of item.links) {
			let a;
			if (item.search) {
				a = ce('a', {href}, label + ': ', ce('b', {}, item.name));
			} else if (label) {
				a = ce('a', {href, className: 'jump-action'}, ce('small', {}, label));
			} else {
				a = ce('a', {href}, ...highlight(item.name));
			}
			li.append(a, ' ');
		}
		if (item.kind) {
			li.append(ce('small', {}, item.kind));
		}
		list.append(li);
	}
	jumpToggle(list.children.length > 0);
	jumpSelect(0, jumpColumn);
}

/** Highlight a link in the list of the search box
* @param {number} row index of <li>, -1 for none
* @param {number} column index of <a> in the row, moved to the last one if there are fewer
*/
function jumpSelect(row, column) {
	const input = qs('#jump input');
	const list = qs('#jump-list');
	input.removeAttribute('aria-activedescendant');
	for (const li of qsa('li', list)) {
		li.setAttribute('aria-selected', false);
		alterClass(qs('.current', li), 'current');
	}
	const li = list.children[row];
	if (li) {
		const links = qsa('a', li);
		li.setAttribute('aria-selected', true);
		alterClass(links[Math.min(column, links.length - 1)], 'current', true);
		input.setAttribute('aria-activedescendant', li.id);
		// not scrollIntoView({block: 'nearest'}) - unsupported in Safari < 14
		if (li.offsetTop < list.scrollTop) {
			list.scrollTop = li.offsetTop;
		} else if (li.offsetTop + li.offsetHeight > list.scrollTop + list.clientHeight) {
			list.scrollTop = li.offsetTop + li.offsetHeight - list.clientHeight;
		}
	}
}

/** Show or hide the list of the search box
* @param {boolean} open
*/
function jumpToggle(open) {
	alterClass(qs('#jump-list'), 'hidden', !open);
	qs('#jump input').setAttribute('aria-expanded', open);
}

/** Move in the list of the search box by arrows and PgUp/PgDn, open the link by Enter, leave the box by Esc
* @param {KeyboardEvent} event
* @return {boolean|undefined} false if the key was handled
* @this HTMLInputElement
*/
function jumpKeydown(event) {
	const list = qs('#jump-list');
	if (event.isComposing || event.altKey) {
		return;
	}
	if (isEscape(event)) {
		this.blur(); // also closes the list and unfloats the box
		return false;
	}
	if (!list || list.classList.contains('hidden')) {
		return;
	}
	const rows = list.children;
	const li = qs('[aria-selected="true"]', list);
	const row = [...rows].indexOf(li);
	const links = (li ? qsa('a', li) : []);
	const column = [...links].indexOf(qs('.current', li || list));
	if (/^Arrow(Down|Up)$/.test(event.key) && !isCtrl(event)) {
		jumpSelect((row + (event.key == 'ArrowUp' ? rows.length - 1 : 1)) % rows.length, jumpColumn);
		return false;
	}
	if (/^Page(Down|Up)$/.test(event.key) && !isCtrl(event)) {
		const page = Math.max(1, Math.floor(list.clientHeight / li.offsetHeight)); // the rows visible in the list
		jumpSelect(Math.max(0, Math.min(rows.length - 1, row + (event.key == 'PageUp' ? -page : page))), jumpColumn);
		return false;
	}
	if (/^Arrow(Left|Right)$/.test(event.key) && !isCtrl(event) && !event.shiftKey && this.selectionStart == this.value.length) { // elsewhere the arrows move the caret
		const next = column + ((event.key == 'ArrowLeft') == (document.dir != 'rtl') ? -1 : 1);
		if (links[next]) { // otherwise Left moves the caret from the name
			jumpColumn = next;
			cookie('adminer_jump=' + jumpColumn, 365);
			jumpSelect(row, jumpColumn);
			return false;
		}
	}
	if (event.key == 'Enter' && links[column]) {
		const href = links[column].href;
		if (isCtrl(event) || event.shiftKey) {
			open(href);
		} else {
			location.href = href;
		}
		return false;
	}
}
