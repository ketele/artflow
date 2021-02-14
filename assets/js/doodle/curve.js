import {Utils} from '../utils';

export class Curve {
    constructor(cp1x = null, cp1y = null, cp2x = null, cp2y = null, x = null, y = null, angle = null) {
        this.cp1X = cp1x;
        this.cp1Y = cp1y;
        this.cp2X = cp2x;
        this.cp2Y = cp2y;
        this.x = x;
        this.y = y;
        this.angle = angle;
    }

    get param() {
        return this.curveParam();
    }

    /**
     * cp1x, cp1y, cp2x, cp2y - curve coordinates
     * x, y - place coordinates
     * @returns {{cp1x: *, cp1y: *, cp2x: *, cp2y: *, x: *, y: *}}
     */

    curveParam() {
        return {
            cp1x: this.cp1X,
            cp1y: this.cp1Y,
            cp2x: this.cp2X,
            cp2y: this.cp2Y,
            x: this.x,
            y: this.y
        };
    }

    getFirstSmoothCurve(previousCurve) {
        if (previousCurve.cp2X - previousCurve.x < 0) {
            this.cp1X = previousCurve.x + (previousCurve.x - previousCurve.cp2X);
        } else {
            this.cp1X = previousCurve.x - (previousCurve.cp2X - previousCurve.x);
        }

        if (previousCurve.cp2Y - previousCurve.y < 0) {
            this.cp1Y = previousCurve.y - (previousCurve.cp2Y - previousCurve.y);
        } else {
            this.cp1Y = previousCurve.y + (previousCurve.y - previousCurve.cp2Y);
        }
    }

    genSecondCoordinates(previousCurve, radius) {
        const nodeAngle = Utils.getRandomFloat(previousCurve.angle + Math.PI, previousCurve.angle + (2 * Math.PI));

        this.cp2X = previousCurve.x - radius + (Utils.getRandomInt(-radius / 2, radius / 2) * Math.sin(nodeAngle));
        this.cp2Y = previousCurve.y + (Utils.getRandomInt(-radius / 2, radius / 2) * Math.cos(nodeAngle));
    }

    checkLineIntersection(previousCurve) {
        let a, b;
        const result = {
            x: null,
            y: null,
            onLine1: false,
            onLine2: false
        };
        const denominator = ((this.y - this.cp2Y) * (this.cp1X - previousCurve.x)) - ((this.x - this.cp2X) * (this.cp1Y - previousCurve.y));

        a = previousCurve.y - this.cp2Y;
        b = previousCurve.x - this.cp2X;
        const numerator1 = ((this.x - this.cp2X) * a) - ((this.y - this.cp2Y) * b);
        const numerator2 = ((this.cp1X - previousCurve.x) * a) - ((this.cp1Y - previousCurve.y) * b);
        a = numerator1 / denominator;
        b = numerator2 / denominator;

        // if we cast these lines infinitely in both directions, they intersect here:
        result.x = previousCurve.x + (a * (this.cp1X - previousCurve.x));
        result.y = previousCurve.y + (a * (this.cp1Y - previousCurve.y));

        // if line1 is a segment and line2 is infinite, they intersect if:
        if (a > 0 && a < 1) {
            result.onLine1 = true;
        }
        // if line2 is a segment and line1 is infinite, they intersect if:
        if (b > 0 && b < 1) {
            result.onLine2 = true;
        }
        // if line1 and line2 are segments, they intersect if both of the above are true
        if (result.onLine1 && result.onLine2) {
            this.cp1X = result.x;
            this.cp1Y = result.y;
            this.cp2X = result.x;
            this.cp2Y = result.y;
        }
    }
}
