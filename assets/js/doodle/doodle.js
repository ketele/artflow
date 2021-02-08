import {Curve} from "./curve";
import {Utils} from "../utils";

/**
 *
 */

export class Doodle {
    constructor(centerX = 200, centerY = 250, radius = 100) {
        this.centerX = centerX;
        this.centerY = centerY;
        this.angleMin = 0.1;
        this.angleMax = 2.9;
        this.angle = Utils.getRandomFloat(this.angleMin, this.angleMax);
        this.radius = radius;
        this.curves = [];
        this.length = 0;
        this.width = 0;
        this.height = 0;
    }

    setwWidth(width) {
        this.width = width;
    }

    setwHeight(height) {
        this.height = height;
    }

    /**
     *
     * @param angleMax
     */

    generateNodes(angleMax = 2 * Math.PI) {
        this.resetCurves();
        let x, y;
        let startX = this.centerX + Utils.getRandomInt(-this.radius, this.radius),
            startY = this.centerY + Utils.getRandomInt(-this.radius, this.radius);
        let newX = startX, newY = startY;

        this.curves.push(new Curve(newX, newY, newX, newY, newX, newY, this.angle));
        this.length = 1;

        while (this.angle <= angleMax) {
            // calculate x, y from a vector with known length and angle
            x = this.radius * Math.cos(this.angle);
            y = this.radius * Math.sin(this.angle);
            newX = this.centerX + x;
            newY = this.centerY + y;

            this.curves.push(new Curve(newX, newY, newX, newY, newX, newY, this.angle));
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
                    coordinates.doodle[i].cp1X * transformationFactorX,
                    coordinates.doodle[i].cp1Y * transformationFactorY,
                    coordinates.doodle[i].cp2X * transformationFactorX,
                    coordinates.doodle[i].cp2Y * transformationFactorY,
                    coordinates.doodle[i].x * transformationFactorX,
                    coordinates.doodle[i].y * transformationFactorY,
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

        let cp1X;
        let cp1Y;
        let cp2X;
        let cp2Y;
        let x;
        let y;
        let angle;

        let tempCp1X;
        let tempCp1Y;
        let tempCp2X;
        let tempCp2Y;

        let addedNodes = false;
        let isFirstRound = true;

        let i = 0;
        while (i < this.curves.length) {
            if (typeof this.curves[i] === "undefined" || this.curves[i].x <= this.centerX || !isFirstRound) {
                if (addedNodes && typeof this.curves[i] !== "undefined") {
                    isFirstRound = false;

                    tempCp1X = this.curves[i].cp1X;
                    tempCp1Y = this.curves[i].cp1Y;
                    tempCp2X = this.centerX - (this.curves[i].cp1X - this.centerX);
                    tempCp2Y = tempCp1Y;
                }

                this.curves.splice(i, 1);
                this.length--;
            } else {
                if (addedNodes === false) {
                    this.curves[i].cp1X = this.centerX - (this.curves[i].cp2X - this.centerX);
                    this.curves[i].cp1Y = this.curves[i].cp2Y;
                }

                addedNodes = true;
                i++;
            }
        }

        length = this.curves.length;

        if (isFirstRound && typeof tempCp1X === "undefined" && this.curves.length > 0) {
            tempCp1X = this.curves[length - 1].cp1X;
            tempCp1Y = this.curves[length - 1].cp1Y;
            tempCp2X = this.centerX - (this.curves[length - 1].cp1X - this.centerX);
            tempCp2Y = tempCp1Y;
        }

        for (let i = 0; i < length; i++) {
            let opposedI = length - i - 1;
            x = this.centerX - (this.curves[opposedI].x - this.centerX);
            y = this.curves[opposedI].y;
            if (i === 0) {
                cp1X = tempCp1X;
                cp1Y = tempCp1Y;
                cp2X = tempCp2X;
                cp2Y = tempCp2Y;
            } else {
                cp2X = this.centerX - (this.curves[opposedI + 1].cp1X - this.centerX);
                cp2Y = this.curves[opposedI + 1].cp1Y;
                cp1X = this.centerX - (this.curves[opposedI + 1].cp2X - this.centerX);
                cp1Y = this.curves[opposedI + 1].cp2Y;
            }
            angle = this.curves[opposedI].angle;

            this.curves.push(new Curve(cp1X, cp1Y, cp2X, cp2Y, x, y, angle));

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
        for (var i = 0; i < this.length; i++) {
            this.curves[i].genSecondCoordinates(this.curves[(i + this.length - 1) % this.length], this.radius);
        }

        for (var i = 0; i < this.length; i++) {
            this.curves[i].getFirstSmoothCurve(this.curves[(i + this.length - 1) % this.length]);
        }
    }

    updateCoordinates(ctx) {
        for (var i = 0; i < this.curves.length; i++) {
            this.curves[i].checkLineIntersection(this.curves[(i + this.curves.length - 1) % this.curves.length]);
        }
    }

    resetCurves() {
        //this.curves = [];
        this.angle = Utils.getRandomFloat(this.angleMin, this.angleMax);
        this.curves.splice(0, this.curves.length);
        this.length = 0;
    }

    drawInfo(ctx) {
        ctx.beginPath();
        ctx.moveTo(this.curves[this.length - 1].x, this.curves[this.length - 1].y);
        for (var i = 0; i < this.length; i++) {
            ctx.lineTo(this.curves[i].cp1X, this.curves[i].cp1Y);
            ctx.lineTo(this.curves[i].cp2X, this.curves[i].cp2Y);
            ctx.lineTo(this.curves[i].x, this.curves[i].y);
            ctx.fillStyle = "#8899ff";
            ctx.font = "15px Arial";
            ctx.fillText("cp1: " + i + 1 + ". (" + Math.round(this.curves[i].cp1X) + "," + Math.round(this.curves[i].cp1Y) + ")", this.curves[i].cp1X, this.curves[i].cp1Y);
            ctx.fillStyle = "#00cc22";
            ctx.fillText("cp2: " + i + 2 + ". (" + Math.round(this.curves[i].cp2X) + "," + Math.round(this.curves[i].cp2Y) + ")", this.curves[i].cp2X, this.curves[i].cp2Y);
            ctx.fillStyle = "#ff8899";
            ctx.fillText(
                "xy: " + parseInt(i)
                + ". (" + Math.round(this.curves[i].x)
                + ","
                + Math.round(this.curves[i].y)
                + ")", this.curves[i].x, ((this.curves[i].x <= this.centerX) ? this.curves[i].y + 10 : this.curves[i].y)
            );
        }
        ctx.strokeStyle = '#8899ff';
        ctx.stroke();
    }

    drawAngles(ctx) {
        for (let i = 0; i < this.length; i++) {
            ctx.drawCircle(this.curves[i].x, this.curves[i].y, this.curves[i].angle + Math.PI, this.curves[i].angle + (2 * Math.PI));
        }
    }

    draw(ctx) {
        ctx.beginPath();
        ctx.moveTo(this.curves[this.length - 1].x, this.curves[this.length - 1].y);

        for (let i = 0; i < this.length; i++) {
            ctx.bezierCurveTo(
                this.curves[i].cp1X,
                this.curves[i].cp1Y,
                this.curves[i].cp2X,
                this.curves[i].cp2Y,
                this.curves[i].x,
                this.curves[i].y
            );
        }

        ctx.fillStyle = "#bbd2d2";
        ctx.strokeStyle = "#bbd2d2";
        ctx.stroke();
        ctx.fill('nonzero');
    }

    prinCurvesParam() {
        for (let i = 0; i < this.length; i++) {
            console.log(
                'test: '
                + ' cp1x:' + this.curves[i].cp1X
                + ', cp1y:' + this.curves[i].cp1Y
                + ' cp2x:' + this.curves[i].cp2X
                + ', cp2y:' + this.curves[i].cp2Y
                + ',  x:' + this.curves[i].x
                + ', y:' + this.curves[i].y
                + ', angle: '
                + this.curves[i].angle
            );
        }
    }

    drawCenter(ctx) {
        ctx.drawCircle(this.centerX, this.centerY, 0, 2 * Math.PI, 10);
    }

    clearCanvas(ctx) {
        ctx.clearRect(0, 0, ctx.canvas.width, ctx.canvas.height);
    }
}