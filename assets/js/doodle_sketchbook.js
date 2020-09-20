import {Doodle} from "./doodle/Doodle";
import {Workspace} from "./sketchbook/Workspace";

CanvasRenderingContext2D.prototype.drawCircle = function( centerX, centerY, angle_begin = 0, angle_end = 2 * Math.PI, radius = 50 ){
    this.radius = radius;
    this.beginPath();
    this.strokeStyle = '#8ED6FF';
    this.arc(centerX, centerY, radius, angle_begin, angle_end, false);
    this.restore();
    this.stroke();
    this.strokeStyle = 'black';
};

class DoodleSketchbook {
    loadDoodle(){
        this.canvas = document.getElementById('doodle-sketchbook');
        this.ctx = this.canvas.getContext('2d');

        this.size = 600;

        if( this.canvas.parentElement.offsetWidth < this.size )
            this.size = this.canvas.parentElement.offsetWidth;

        this.ctx.canvas.width  = this.size;
        this.ctx.canvas.height = this.size;

        this.doodle = new Doodle( this.canvas.offsetWidth/2, this.canvas.offsetHeight/2, this.canvas.offsetHeight/4 );

        this.doodle.generateUnbalancedNodes();
        this.doodle.draw(this.ctx);
    }

    loadSketchbook(){
        this.canvas_sketchbook = document.getElementById('sketchbook');
        this.workspace = new Workspace(this.canvas_sketchbook);
        this.workspace.run();

    }
}

window.addEventListener('load', e => {
    let doodle_sketchbook = new DoodleSketchbook();
    doodle_sketchbook.loadDoodle();
    doodle_sketchbook.loadSketchbook();

    document.getElementById('refresh-doodle').addEventListener('click', e => {
       doodle_sketchbook.doodle.clearCanvas(doodle_sketchbook.ctx);
        doodle_sketchbook.loadDoodle();
    } );

    document.getElementById('clear-sketchbook').addEventListener('click', e => {
       doodle_sketchbook.workspace.clearCanvas();
    } );

    document.getElementById('download-image').addEventListener('click', e => {
       doodle_sketchbook.workspace.putImage();
    } );

    document.getElementById('pencil').addEventListener('click', e => {
        doodle_sketchbook.workspace.setTool('pencil');
    } );

    document.getElementById('eraser').addEventListener('click', e => {
        doodle_sketchbook.workspace.setTool('eraser');
    } );

    document.getElementById('opacity').addEventListener('change', e => {
        doodle_sketchbook.workspace.setToolOpacity(e.target.value);
    } );

    document.getElementById('size').addEventListener('change', e => {
        doodle_sketchbook.workspace.setToolSize(e.target.value);
    } );
});