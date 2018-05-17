/*
 * Bookmarks_FullTextSearch - Indexing bookmarks
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Maxence Lange <maxence@artificial-owl.com>
 * @copyright 2018
 * @license GNU AGPL version 3 or any later version
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 */


/** global: OCA */

const fullTextSearch = OCA.FullTextSearch.api;


var elements = {
	old_bookmarks: null,
	search_result: null,
	template_entry: null
};


const Bookmarks_FullTextSearch = function () {
	this.init();
};


Bookmarks_FullTextSearch.prototype = {

	init: function () {
		var self = this;

		elements.old_bookmarks = $('.bookmarks_list');

		elements.search_result = $('<div>');
		elements.search_result.insertBefore(elements.old_bookmarks);

		elements.search_input = $('#next_search_input');

		elements.template_entry = self.generateTemplateEntry();
		fullTextSearch.setEntryTemplate(elements.template_entry);
		fullTextSearch.setResultContainer(elements.search_result);
		fullTextSearch.initFullTextSearch('bookmarks', 'bookmarks', self);
	},


	generateTemplateEntry: function () {

		var divLeft = $('<div>', {class: 'result_entry_left'});
		divLeft.append($('<div>', {id: 'title'}));
		divLeft.append($('<div>', {id: 'line1'}));
		divLeft.append($('<div>', {id: 'line2'}));

		var divRight = $('<div>', {class: 'result_entry_right'});
		divRight.append($('<div>', {id: 'score'}));

		var divDefault = $('<div>', {class: 'result_entry_default'});
		divDefault.append(divLeft);
		divDefault.append(divRight);

		return $('<div>').append(divDefault);
	},


	onEntryGenerated: function (entry) {
		entry.off('click').on('click', function () {
			window.open(entry.attr('data-link'), '_blank');
		});
	},


	onResultDisplayed: function () {
		elements.old_bookmarks.fadeOut(150, function () {
			elements.search_result.fadeIn(150);
		});
	},


	onSearchReset: function () {
		elements.search_result.fadeOut(150, function () {
			elements.old_bookmarks.fadeIn(150);
		});
	},


	onResultClose: function () {
		elements.search_result.fadeOut(150, function () {
			elements.old_bookmarks.fadeIn(150);
		});
	}

};


OCA.FullTextSearch.Bookmarks = Bookmarks_FullTextSearch;

$(document).ready(function () {
	OCA.FullTextSearch.navigate = new Bookmarks_FullTextSearch();
});



