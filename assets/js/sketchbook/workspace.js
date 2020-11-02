import {Utils} from "../utils";

export class Workspace {
    constructor (canvas) {

        this.mousePos1 = { x: 0, y: 0};
        this.mousePos2 = { x: 0, y: 0};
        this.draw = false;
        this.canvas = canvas;
        this.isIOS = Utils.iOS();

        this.ctx = this.canvas.getContext('2d');

        this.size = 600;

        if( this.canvas.parentElement.offsetWidth < this.size )
            this.size = this.canvas.parentElement.offsetWidth;

        this.ctx.canvas.width  = this.size;
        this.ctx.canvas.height = this.size;

        this.width = this.size;
        this.height = this.size;

        this.imageData = this.ctx.getImageData(0, 0, this.canvas.offsetWidth, this.canvas.offsetHeight);
        this.data = this.imageData.data;

        this.imageLayer = [...this.data];
        this.toolLayer = new Array( this.width * this.height * 4 ).fill(0);

        this.toolSize = 1;
        this.lineColor = { r: 0, g: 0, b:0, a: 255};
        this.tool = 'pencil';
    }

    /**
     * imageData.data[i + 0] = $r;      // R value
     * imageData.data[i + 1] = $g;      // G value
     * imageData.data[i + 2] = $b;      // B value
     * imageData.data[i + 3] = $a;      // A value
     * @param x1
     * @param y1
     * @param x2
     * @param y2
     * @constructor
     */

    BresenhamLine(x1, y1, x2, y2) {
        let d, dx, dy, ai, bi, xi, yi;
        let x = x1, y = y1;


        if (x1 < x2) {
            xi = 1;
            dx = x2 - x1;
        }
        else {
            xi = -1;
            dx = x1 - x2;
        }
        // ustalenie kierunku rysowania
        if (y1 < y2) {
            yi = 1;
            dy = y2 - y1;
        }
        else {
            yi = -1;
            dy = y1 - y2;
        }

        if( x < 0 || x >= this.width )
            return null;

        this.updateLine(x, y);

        if (dx > dy) {
            ai = (dy - dx) * 2;
            bi = dy * 2;
            d = bi - dx;
            while (x != x2) {
                if (d >= 0) {
                    x += xi;
                    y += yi;
                    d += ai;
                }
                else {
                    d += bi;
                    x += xi;
                }

                if( x < 0 || x >= this.width )
                    return null;

                this.updateLine(x, y);
            }
        }

        else {
            ai = (dx - dy) * 2;
            bi = dx * 2;
            d = bi - dy;

            while (y != y2) {

                if (d >= 0) {
                    x += xi;
                    y += yi;
                    d += ai;
                }
                else {
                    d += bi;
                    y += yi;
                }

                if( x < 0 || x >= this.width )
                    return null;

                this.updateLine(x, y);
            }
        }

        if( this.tool === 'pencil' ) {
            this.imageLayer.forEach((data, i) => {
                this.data[i] = data + this.toolLayer[i];
            });
        }else{
            this.imageLayer.forEach( (data, i) => {
                this.data[i] = data - this.toolLayer[i];
            });
        }
    }

    updateLine(x, y){
        let index = (y * this.width + x) * 4;
        let tool_x = 0;
        let tool_index = 0;

        if( this.toolSize > 1 ){
                for(let tx = 0; tx < this.toolSize * 4; tx+=4)
                    for(let ty = 0; ty < this.toolSize; ty++)
                    {
                        tool_x = tx - ( Math.floor(this.toolSize / 2) * 4);
                        tool_index =  index + ( ( ty - Math.floor(this.toolSize / 2) ) * this.width * 4 ) + tool_x;
                        //this.toolLayer[tool_index] = this.lineColor.r;
                        //this.toolLayer[tool_index + 1] = this.lineColor.g;
                        //this.toolLayer[tool_index + 2] = this.lineColor.b;
                        if( x + tool_x > 0 && x + tool_x < this.width )
                            this.toolLayer[tool_index + 3] = this.lineColor.a;
                    }
            }else{
                //this.toolLayer[index] = this.lineColor.r;
                //this.toolLayer[index + 1] = this.lineColor.g;
                //this.toolLayer[index + 2] = this.lineColor.b;
                this.toolLayer[index + 3] = this.lineColor.a;
            }
    }

    getMousePos(evt) {
        let rect = this.canvas.getBoundingClientRect();
        return {
            x: evt.clientX - rect.left,
            y: evt.clientY - rect.top
        };
    }

    getTouchePos(evt) {
        let rect = this.canvas.getBoundingClientRect();
        return {
            x: evt.touches[0].clientX - rect.left,
            y: evt.touches[0].clientY - rect.top
        };
    }

    drag(evt) {
        evt.stopPropagation();
        evt.stopImmediatePropagation();
        this.mousePos1 = this.getMousePos(evt);
    }

    toucheDrag(evt) {
        evt.stopPropagation();
        evt.stopImmediatePropagation();
        this.mousePos1 = this.getTouchePos(evt);
    }

    dragStart(evt) {
        evt.stopPropagation();
        evt.stopImmediatePropagation();
        this.mousePos1 = this.getMousePos(evt);
        this.mousePos2 = this.mousePos1;
        this.draw = true;
    }

    toucheDragStart(evt) {
        evt.stopPropagation();
        evt.stopImmediatePropagation();
        this.mousePos1 = this.getTouchePos(evt);
        this.mousePos2 = this.mousePos1;
        this.draw = true;
    }

    dragStop(evt) {
        evt.stopPropagation();
        evt.stopImmediatePropagation();
        this.draw = false;

        this.imageLayer = [...this.imageData.data];
        this.toolLayer.fill(0);
    }

    run(){
        this.loop();

        if( this.isIOS ){
            this.canvas.addEventListener('mousemove', evt => this.drag(evt), false);
            this.canvas.addEventListener('mousedown', evt => this.dragStart(evt), false);
            document.addEventListener('mouseup', evt => this.dragStop(evt), false);

            this.canvas.addEventListener('touchmove', evt => this.toucheDrag(evt), false);
            this.canvas.addEventListener('touchstart', evt => this.toucheDragStart(evt), false);
            document.addEventListener('touchend', evt => this.dragStop(evt), false);
        }else{
            this.canvas.addEventListener('touchcancel', evt => evt.preventDefault(), false);
            this.canvas.addEventListener('touchmove', evt => evt.preventDefault(), false);
            this.canvas.addEventListener('touchstart', evt => evt.preventDefault(), false);
            this.canvas.addEventListener('touchend', evt => evt.preventDefault(), false);

            document.addEventListener('pointermove', evt => this.drag(evt), false);
            document.addEventListener('pointerdown', evt => this.dragStart(evt), false);
            document.addEventListener('pointerup', evt => this.dragStop(evt), false);
        }
    }

    loop(){
        if(this.draw){
            this.BresenhamLine(parseInt(this.mousePos2.x),parseInt(this.mousePos2.y),parseInt(this.mousePos1.x),parseInt(this.mousePos1.y) );
            this.ctx.putImageData(this.imageData, 0, 0);
            //this.ctx.fillStyle = 'red';
            //this.ctx.fillText(this.mousePos2.x + " , " + this.mousePos2.y,this.mousePos2.x,this.mousePos2.y);
        }


        this.mousePos2 = this.mousePos1;

        window.requestAnimationFrame(() => this.loop());
    }

    clearCanvas(){
        this.imageData.data.fill(0);
        this.ctx.putImageData(this.imageData, 0, 0);
        this.imageLayer = [...this.imageData.data];
    }

    setTool(tool){
        this.tool = tool;
    }

    setToolSize(tool_size){
        this.toolSize = parseInt(tool_size) * 2 - 1;
    }

    setToolOpacity(tool_opacity){
        this.lineColor.a = parseInt(tool_opacity);
    }

    putWorkspaceImage(){
        let image = this.canvas.toDataURL("image/png");//.replace("image/png", "image/octet-stream");

        window.location.href = image;
    }

}
