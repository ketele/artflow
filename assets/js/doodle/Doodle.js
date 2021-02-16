import {Curve} from '../sketchbook/Curve';
import {Point2D} from '../sketchbook/Point2D';
import {Utils} from '../Utils';

export class Doodle {
    constructor(center = new Point2D(250, 250), radius = 100) {
        this.center = center;
        this.angleMin = 0.1;
        this.angleMax = 2.9;
        this.angle = Utils.getRandomFloat(this.angleMin, this.angleMax);
        this.radius = radius;
        this.curves = [];
        this.length = 0;
        this.width = 0;
        this.height = 0;
    }

    generateNodes(angleMax = 2 * Math.PI) {
        this.resetCurves();
        const point = new Point2D(
            this.center.x + Utils.getRandomInt(-this.radius, this.radius),
            this.center.y + Utils.getRandomInt(-this.radius, this.radius)
        );

        this.curves.push(new Curve({...point}, {...point}, {...point}, this.angle));
        this.length = 1;

        while (this.angle <= angleMax) {
            // calculate x, y from a vector with known length and angle
            point.x = this.center.x + (this.radius * Math.cos(this.angle));
            point.y = this.center.y + (this.radius * Math.sin(this.angle));

            this.curves.push(new Curve({...point}, {...point}, {...point}, this.angle));
            this.angle += Utils.getRandomFloat(this.angleMin, this.angleMax);
            this.length++;
        }
    }

    setNodes(coordinates) {
        let transformationFactorX = 1;
        let transformationFactorY = 1;

        if (coordinates.size !== this.width) {
            transformationFactorX = this.width / coordinates.size;
            transformationFactorY = this.height / coordinates.size;
        }

        for (let i = 0; i < coordinates.doodle.length; i++) {
            this.curves.push(
                new Curve(
                    new Point2D(coordinates.doodle[i].cp1.x * transformationFactorX, coordinates.doodle[i].cp1.y * transformationFactorY),
                    new Point2D(coordinates.doodle[i].cp2.x * transformationFactorX, coordinates.doodle[i].cp2.y * transformationFactorY),
                    new Point2D(coordinates.doodle[i].end.x * transformationFactorX, coordinates.doodle[i].end.y * transformationFactorY),
                    coordinates.doodle[i].angle
                )
            );
        }
        this.length = coordinates.doodle.length;
    }

    generateSymmetricalNodes() {
        this.generateNodes(Math.PI);
        this.generateCoordinates();
        this.updateCoordinates();

        let cp1 = new Point2D();
        let cp2 = new Point2D();
        const endPoint = new Point2D();
        let angle;

        let tempCp1 = new Point2D();
        const tempCp2 = new Point2D();

        let addedNodes = false;
        let isFirstRound = true;

        let i = 0;
        while (i < this.curves.length) {
            if (typeof this.curves[i] === 'undefined' || this.curves[i].end.x <= this.center.x || !isFirstRound) {
                if (addedNodes && typeof this.curves[i] !== 'undefined') {
                    isFirstRound = false;

                    tempCp1 = {...this.curves[i].cp1};
                    tempCp2.x = this.center.x - (this.curves[i].cp1.x - this.center.x);
                    tempCp2.y = tempCp1.y;
                }

                this.curves.splice(i, 1);
                this.length--;
            } else {
                if (addedNodes === false) {
                    this.curves[i].cp1.x = this.center.x - (this.curves[i].cp2.x - this.center.x);
                    this.curves[i].cp1.y = this.curves[i].cp2.y;
                }

                addedNodes = true;
                i++;
            }
        }

        const length = this.curves.length;

        if (isFirstRound && tempCp1.x === null && this.curves.length > 0) {
            tempCp1 = {...this.curves[length - 1].cp1};
            tempCp2.x = this.center.x - (this.curves[length - 1].cp1.x - this.center.x);
            tempCp2.y = tempCp1.y;
        }

        for (let i = 0; i < length; i++) {
            const opposedI = length - i - 1;
            endPoint.x = this.center.x - (this.curves[opposedI].end.x - this.center.x);
            endPoint.y = this.curves[opposedI].end.y;
            if (i === 0) {
                cp1 = {...tempCp1};
                cp2 = {...tempCp2};
            } else {
                cp2.x = this.center.x - (this.curves[opposedI + 1].cp1.x - this.center.x);
                cp2.y = this.curves[opposedI + 1].cp1.y;
                cp1.x = this.center.x - (this.curves[opposedI + 1].cp2.x - this.center.x);
                cp1.y = this.curves[opposedI + 1].cp2.y;
            }
            angle = this.curves[opposedI].angle;

            this.curves.push(new Curve({...cp1}, {...cp2}, {...endPoint}, angle));

            this.length++;
        }

        this.length = this.curves.length;

        if (this.curves.length < 3) {
            this.generateSymmetricalNodes();
        }
    }

    generateUnbalancedNodes() {
        this.generateNodes();
        this.generateCoordinates();
        this.updateCoordinates();
    }

    generateCoordinates() {
        for (let i = 0; i < this.length; i++) {
            this.curves[i].genSecondCoordinates(this.curves[(i + this.length - 1) % this.length], this.radius);
        }

        for (let i = 0; i < this.length; i++) {
            this.curves[i].getFirstSmoothCurve(this.curves[(i + this.length - 1) % this.length]);
        }
    }

    updateCoordinates() {
        for (let i = 0; i < this.curves.length; i++) {
            this.curves[i].checkLineIntersection(this.curves[(i + this.curves.length - 1) % this.curves.length]);
        }
    }

    resetCurves() {
        this.angle = Utils.getRandomFloat(this.angleMin, this.angleMax);
        this.curves.splice(0, this.curves.length);
        this.length = 0;
    }

    draw(ctx) {
        ctx.beginPath();
        ctx.moveTo(this.curves[this.length - 1].end.x, this.curves[this.length - 1].end.y);

        for (let i = 0; i < this.length; i++) {
            ctx.bezierCurveTo(
                this.curves[i].cp1.x,
                this.curves[i].cp1.y,
                this.curves[i].cp2.x,
                this.curves[i].cp2.y,
                this.curves[i].end.x,
                this.curves[i].end.y
            );
        }

        ctx.fillStyle = '#bbd2d2';
        ctx.strokeStyle = '#bbd2d2';
        ctx.stroke();
        ctx.fill('nonzero');
    }

    clearCanvas(ctx) {
        ctx.clearRect(0, 0, ctx.canvas.width, ctx.canvas.height);
    }
}
