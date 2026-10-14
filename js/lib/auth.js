/**
 * Token injection logic required for ajax requests with Nextcloud 34+ since they removed their upstream jquery
 * as well as the former oc_requesttoken (with Nextcloud 35+). See https://github.com/nextcloud/passman/issues/869
 */
function GetRequestToken() {
	if (typeof OC !== 'undefined' && OC.requestToken) {
		return OC.requestToken;
	}
	if (typeof _nc_auth_requestToken === 'string' && _nc_auth_requestToken) {
		return _nc_auth_requestToken;
	}
	const head = document.head || document.getElementsByTagName('head')[0];
	return (head && head.dataset.requesttoken) ? head.dataset.requesttoken : '';
}
