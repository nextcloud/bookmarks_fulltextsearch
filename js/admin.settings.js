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

/** global: OC */
/** global: bookmarks_elements */
/** global: fts_admin_settings */



var bookmarks_settings = {

	config: null,

	refreshSettingPage: function () {

		$.ajax({
			method: 'GET',
			url: OC.generateUrl('/apps/bookmarks_fulltextsearch/admin/settings')
		}).done(function (res) {
			bookmarks_settings.updateSettingPage(res);
		});

	},


	updateSettingPage: function (result) {
		bookmarks_elements.bookmarks_ttl.val(result.bookmarks_ttl);

		fts_admin_settings.tagSettingsAsSaved(bookmarks_elements.bookmarks_div);
	},


	saveSettings: function () {

		var data = {
			bookmarks_ttl: bookmarks_elements.bookmarks_ttl.val()
		};

		$.ajax({
			method: 'POST',
			url: OC.generateUrl('/apps/bookmarks_fulltextsearch/admin/settings'),
			data: {
				data: data
			}
		}).done(function (res) {
			bookmarks_settings.updateSettingPage(res);
		});

	}


};
