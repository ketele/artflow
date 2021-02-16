import {Utils} from '../Utils';
import {Point2D} from './Point2D';

export class Curve {
    constructor(cp1 = new Point2D(), cp2 = new Point2D(), end = new Point2D(), angle = null) {
        this.cp1 = cp1;
        this.cp2 = cp2;
        this.end = end;
        this.angle = angle;
    }

    getFirstSmoothCurve(previousCurve) {
        if (previousCurve.cp2.x - previousCurve.end.x < 0) {
            this.cp1.x = previousCurve.end.x + (previousCurve.end.x - previousCurve.cp2.x);
        } else {
            this.cp1.x = previousCurve.end.x - (previousCurve.cp2.x - previousCurve.end.x);
        }

        if (previousCurve.cp2.y - previousCurve.end.y < 0) {
            this.cp1.y = previousCurve.end.y - (previousCurve.cp2.y - previousCurve.end.y);
        } else {
            this.cp1.y = previousCurve.end.y + (previousCurve.end.y - previousCurve.cp2.y);
        }
    }

    genSecondCoordinates(previousCurve, radius) {
        const nodeAngle = Utils.getRandomFloat(previousCurve.angle + Math.PI, previousCurve.angle + (2 * Math.PI));

        this.cp2.x = previousCurve.end.x - radius + (Utils.getRandomInt(-radius / 2, radius / 2) * Math.sin(nodeAngle));
        this.cp2.y = previousCurve.end.y + (Utils.getRandomInt(-radius / 2, radius / 2) * Math.cos(nodeAngle));
    }

    checkLineIntersection(previousCurve) {
        let a, b;
        const result = {
            point: new Point2D(),
            onLine1: false,
            onLine2: false
        };

        const denominator = (
            (this.end.y - this.cp2.y) * (this.cp1.x - previousCurve.end.x)
        ) - (
            (this.end.x - this.cp2.x) * (this.cp1.y - previousCurve.end.y)
        );

        a = previousCurve.end.y - this.cp2.y;
        b = previousCurve.end.x - this.cp2.x;
        const numerator1 = ((this.end.x - this.cp2.x) * a) - ((this.end.y - this.cp2.y) * b);
        const numerator2 = ((this.cp1.x - previousCurve.end.x) * a) - ((this.cp1.y - previousCurve.end.y) * b);
        a = numerator1 / denominator;
        b = numerator2 / denominator;

        // if we cast these lines infinitely in both directions, they intersect here:
        result.point.x = previousCurve.end.x + (a * (this.cp1.x - previousCurve.end.x));
        result.point.y = previousCurve.end.y + (a * (this.cp1.y - previousCurve.end.y));

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
            this.cp1 = {...result.point};
            this.cp2 = {...result.point};
        }
    }
}
