import {Curve} from "./curve";
import {Utils} from "../utils";

/**
 *
 */

export class Doodle {
    constructor( centerX = 200, centerY = 250,  radius = 100 ){
        this.centerX = centerX;
        this.centerY = centerY;
        this.angleMin = 0.1;
        this.angleMax = 2.9;
        this.angle = Utils.getRandomFloat( this.angleMin, this.angleMax );
        this.radius = radius;
        this.curves = [];
        this.length = 0;
    }

    /**
     *
     * @param angleMax
     */

    generateNodes( angleMax = 2 * Math.PI ){
        let x, y;
        let startX = this.centerX + Utils.getRandomInt(-this.radius,this.radius), startY = this.centerY + Utils.getRandomInt(-this.radius,this.radius);
        let newX = startX, newY = startY;

        this.curves.push( new Curve( newX, newY, newX, newY, newX, newY, this.angle ));
        this.length = 1;

        while ( this.angle <= angleMax )
        {
            // calculate x, y from a vector with known length and angle
            x = this.radius * Math.cos (this.angle);
            y = this.radius * Math.sin (this.angle);
            newX = this.centerX  + x;
            newY = this.centerY + y;

            this.curves.push( new Curve( newX, newY, newX, newY, newX, newY, this.angle ) );
            this.angle += Utils.getRandomFloat( this.angleMin, this.angleMax );// angle_stepsize;
            this.length++;
        }
    }

    generateSymmetricalNodes(){
        this.generateNodes( Math.PI );
        this.generateCoordinates();
        this.updateCoordinates();
        let length = this.length;
        /*let y;
        let cp1Y;
        let cp2Y;

        for(let i = 0; i < length; i++){
            y = this.centerY -( this.curves[i].y - this.centerY );
            cp1Y = this.centerY -( this.curves[i].cp1Y - this.centerY );
            cp2Y = this.centerY -( this.curves[i].cp2Y - this.centerY );
            this.curves.push( new Curve( this.curves[i].cp1X, cp1Y, this.curves[i].cp2X, cp2Y, this.curves[i].x, y, this.curves[i].angle ) );
            this.length++;
        }/**/

        let x;
        let cp1X;
        let cp2X;

        for(let i = 0; i < length; i++){
            x = this.centerX -( this.curves[i].x - this.centerX );
            cp1X = this.centerX -( this.curves[i].cp1X - this.centerX );
            cp2X = this.centerX -( this.curves[i].cp2X - this.centerX );
            this.curves.push( new Curve( cp1X, this.curves[i].cp1Y, cp2X, this.curves[i].cp2Y, x, this.curves[i].y, this.curves[i].angle ) );
            this.length++;
        }/**/
    }

    generateUnbalancedNodes(){
        this.generateNodes();
        this.generateCoordinates();
        this.updateCoordinates();
    }

    generateCoordinates(){
        for(var i = 0; i < this.length; i++){
            this.curves[i].genSecondCoordinates(this.curves[ (i + this.length - 1) % this.length ], this.radius);
        }

        for(var i = 0; i < this.length; i++){
            this.curves[i].getFirstSmoothCurve( this.curves[ (i + this.length - 1) % this.length ] );
        }
    }

    updateCoordinates(ctx){
        for(var i = 0; i < this.length; i++)
            this.curves[i].checkLineIntersection( this.curves[ (i + this.length - 1) % this.length ] );
    }

    drawInfo(ctx){
        ctx.beginPath();
        ctx.moveTo( this.curves[ this.length - 1 ].x, this.curves[ this.length - 1 ].y );
        for(var i = 0; i < this.length; i++){
            ctx.lineTo(this.curves[i].cp1X,this.curves[i].cp1Y);
            ctx.lineTo(this.curves[i].cp2X,this.curves[i].cp2Y);
            ctx.lineTo(this.curves[i].x,this.curves[i].y);
            ctx.fillStyle = "#8899ff";
            ctx.fillText( i + 1 + ". ("+Math.round(this.curves[i].cp1X)+","+Math.round(this.curves[i].cp1Y)+")",this.curves[i].cp1X,this.curves[i].cp1Y);
            ctx.fillStyle = "#00cc22";
            ctx.fillText( i + 2 + ". ("+Math.round(this.curves[i].cp2X)+","+Math.round(this.curves[i].cp2Y)+")",this.curves[i].cp2X,this.curves[i].cp2Y);
            ctx.fillStyle = "#ff8899";
            ctx.fillText( i + 3 + ". ("+Math.round(this.curves[i].x)+","+Math.round(this.curves[i].y)+")",this.curves[i].x,this.curves[i].y);
        }
        ctx.strokeStyle = '#8899ff';
        ctx.stroke();
    }

    drawAngles(ctx){
        for(let i = 0; i < this.length; i++){
            ctx.drawCircle( this.curves[i].x, this.curves[i].y, this.curves[i].angle + Math.PI, this.curves[i].angle + ( 2 * Math.PI ) );
        }
    }

    draw(ctx){
        ctx.beginPath();
        ctx.moveTo( this.curves[ this.length - 1 ].x, this.curves[ this.length - 1 ].y );

        for(let i = 0; i < this.length; i++){
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
        ctx.mozFillRule = 'nonzero';
        ctx.fillRule = 'nonzero';
        ctx.stroke();
        ctx.fill('nonzero');
    }

    prinCurvesParam(){
        for(let i=0; i < this.length; i++){
            console.log( 'test: ' + ' cp1x:' + this.curves[i].cp1X + ', cp1y:' + this.curves[i].cp1Y + ' cp2x:' + this.curves[i].cp2X + ', cp2y:' + this.curves[i].cp2Y + ',  x:' + this.curves[i].x + ', y:' + this.curves[i].y + ', angle: ' + this.curves[i].angle);
        }
    }

    drawCenter(ctx){
        ctx.drawCircle( this.centerX, this.centerY, 0, 2 * Math.PI, 10 );
    }

    clearCanvas(ctx){
        ctx.clearRect(0,0, ctx.canvas.width, ctx.canvas.height);
    }
}