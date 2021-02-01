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
        pos = (typeof pos !== 'undefined') ? pos : 0;
        let url_string = window.location.href,
            url = new URL(url_string),
            path = url.pathname,
            parts = path.substr(1).split('/');

        return parts[pos];
    }

    static showLoadingOverlay() {
        let body = document.body;
        body.classList.add("is-loading-overlay-visible");
    }

    static hideLoadingOverlay() {
        let body = document.body;
        body.classList.remove("is-loading-overlay-visible");
    }

    static iOS() {
        return [
                'iPad Simulator',
                'iPhone Simulator',
                'iPod Simulator',
                'iPad',
                'iPhone',
                'iPod'
            ].includes(navigator.platform)
            // iPad on iOS 13 detection
            || (navigator.userAgent.includes("Mac") && "ontouchend" in document)
    }

    static ready(callbackFunc) {
        if (document.readyState !== 'loading') {
            // Document is already ready, call the callback directly
            callbackFunc();
        } else if (document.addEventListener) {
            // All modern browsers to register DOMContentLoaded
            document.addEventListener('DOMContentLoaded', callbackFunc);
        } else {
            // Old IE browsers
            document.attachEvent('onreadystatechange', function () {
                if (document.readyState === 'complete') {
                    callbackFunc();
                }
            });
        }
    }

    static generateListHTML(list) {
        let html = '';

        if (list.length > 0) {
            html = '<ol>';
            for (let i = 0; i < list.length; i++) {
                html += '<li>' + list[i] + '</li>';
            }
            html += '</ol>';
        }

        return html;
    }
    
    static createElementFromHTML(htmlString) {
        var div = document.createElement('div');
        div.innerHTML = htmlString.trim();

        // Change this to div.childNodes to support multiple top-level nodes
        return div.firstChild;
    }
}