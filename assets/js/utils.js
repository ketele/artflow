export class Utils {
    static componentToHex(c) {
        let hex = c.toString(16);
        return hex.length === 1 ? "0" + hex : hex;
    }

    static getRandomInt(min, max) {
        min = Math.ceil(min);
        max = Math.floor(max);
        return Math.floor(Math.random() * (max - min)) + min;
    }

    static getRandomFloat(min, max) {
        return Math.random() * (max - min) + min;
    }

    static getUrlParam(pos) {
        pos = ( typeof pos !== 'undefined' ) ? pos : 0;
        let url_string = window.location.href,
        url = new URL(url_string),
        path = url.pathname,
        parts = path.substr(1).split('/');

        return parts[pos];
    }

    static showLoadingOverlay(){
        let body = document.body;
        body.classList.add("is-loading-overlay-visible");
    }

    static hideLoadingOverlay(){
        let body = document.body;
        body.classList.add("is-loading-overlay-visible");
    }
}