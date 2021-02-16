import {Point2D} from './Point2D';
import {RGBA} from './RGBA';
import {Utils} from '../Utils';
import {ToolContext} from './tool/ToolContext';

export class Workspace {
    constructor(canvas) {
        this.mousePos1 = new Point2D(0, 0);
        this.mousePos2 = new Point2D(0, 0);
        this.draw = false;
        this.canvas = canvas;
        this.isIOS = Utils.iOS();

        this.ctx = this.canvas.getContext('2d');

        this.size = 600;

        if (this.canvas.parentElement.offsetWidth < this.size) {
            this.size = this.canvas.parentElement.offsetWidth;
        }

        this.ctx.canvas.width = this.size;
        this.ctx.canvas.height = this.size;

        this.width = this.size;
        this.height = this.size;

        this.imageData = this.ctx.getImageData(0, 0, this.canvas.offsetWidth, this.canvas.offsetHeight);
        this.data = this.imageData.data;

        this.imageLayer = [...this.data];
        this.toolLayer = new Array(this.width * this.height * 4).fill(0);

        this.toolSize = 1;
        this.lineColor = new RGBA(0, 0, 0, 255);
        this.tool = 'pencil';
        this.toolContext = new ToolContext();
    }

    /**
     * imageData.data[i + 0] = $r;      // R value
     * imageData.data[i + 1] = $g;      // G value
     * imageData.data[i + 2] = $b;      // B value
     * imageData.data[i + 3] = $a;      // A value
     * @param startPoint
     * @param endPoint
     * @constructor
     */

    drawBLine(startPoint = new Point2D(), endPoint = new Point2D()) {
        let d, ai, bi;
        const dPoint = new Point2D();
        const iPoint = new Point2D();
        const point = {...startPoint};

        if (startPoint.x < endPoint.x) {
            iPoint.x = 1;
            dPoint.x = endPoint.x - startPoint.x;
        } else {
            iPoint.x = -1;
            dPoint.x = startPoint.x - endPoint.x;
        }
        // checking the direction of drawing
        if (startPoint.y < endPoint.y) {
            iPoint.y = 1;
            dPoint.y = endPoint.y - startPoint.y;
        } else {
            iPoint.y = -1;
            dPoint.y = startPoint.y - endPoint.y;
        }

        if (point.x < 0 || point.x >= this.width) {
            return null;
        }

        this.drawPoint(point);

        if (dPoint.x > dPoint.y) {
            ai = (dPoint.y - dPoint.x) * 2;
            bi = dPoint.y * 2;
            d = bi - dPoint.x;
            while (point.x !== endPoint.x) {
                if (d >= 0) {
                    point.x += iPoint.x;
                    point.y += iPoint.y;
                    d += ai;
                } else {
                    d += bi;
                    point.x += iPoint.x;
                }

                if (point.x < 0 || point.x >= this.width) {
                    return null;
                }

                this.drawPoint(point);
            }
        } else {
            ai = (dPoint.x - dPoint.y) * 2;
            bi = dPoint.x * 2;
            d = bi - dPoint.y;

            while (point.y !== endPoint.y) {
                if (d >= 0) {
                    point.x += iPoint.x;
                    point.y += iPoint.y;
                    d += ai;
                } else {
                    d += bi;
                    point.y += iPoint.y;
                }

                if (point.x < 0 || point.x >= this.width) {
                    return null;
                }

                this.drawPoint(point);
            }
        }

        this.imageLayer.forEach((data, i) => {
            this.data[i] = this.toolContext.use(data, this.toolLayer[i]);
        });
    }

    drawPoint(point) {
        const index = (point.y * this.width + point.x) * 4;
        let toolX = 0;
        let toolIndex = 0;

        if (this.toolSize > 1) {
            for (let tx = 0; tx < this.toolSize * 4; tx += 4) {
                for (let ty = 0; ty < this.toolSize; ty++) {
                    toolX = tx - (Math.floor(this.toolSize / 2) * 4);
                    toolIndex = index + ((ty - Math.floor(this.toolSize / 2)) * this.width * 4) + toolX;
                    if (point.x + toolX > 0 && point.x + toolX < this.width) {
                        this.toolLayer[toolIndex + 3] = this.lineColor.a;
                    }
                }
            }
        } else {
            this.toolLayer[index + 3] = this.lineColor.a;
        }
    }

    getMousePos(evt) {
        const rect = this.canvas.getBoundingClientRect();
        return new Point2D(
            evt.clientX - rect.left,
            evt.clientY - rect.top
        );
    }

    getTouchePos(evt) {
        const rect = this.canvas.getBoundingClientRect();
        return new Point2D(
            evt.touches[0].clientX - rect.left,
            evt.touches[0].clientY - rect.top
        );
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

    run() {
        this.loop();

        if (this.isIOS) {
            this.canvas.addEventListener('mousemove', evt => this.drag(evt), false);
            this.canvas.addEventListener('mousedown', evt => this.dragStart(evt), false);
            document.addEventListener('mouseup', evt => this.dragStop(evt), false);

            this.canvas.addEventListener('touchmove', evt => this.toucheDrag(evt), false);
            this.canvas.addEventListener('touchstart', evt => this.toucheDragStart(evt), false);
            document.addEventListener('touchend', evt => this.dragStop(evt), false);
        } else {
            this.canvas.addEventListener('touchcancel', evt => evt.preventDefault(), false);
            this.canvas.addEventListener('touchmove', evt => evt.preventDefault(), false);
            this.canvas.addEventListener('touchstart', evt => evt.preventDefault(), false);
            this.canvas.addEventListener('touchend', evt => evt.preventDefault(), false);

            document.addEventListener('pointermove', evt => this.drag(evt), false);
            document.addEventListener('pointerdown', evt => this.dragStart(evt), false);
            document.addEventListener('pointerup', evt => this.dragStop(evt), false);
        }
    }

    loop() {
        if (this.draw) {
            this.drawBLine(
                new Point2D(parseInt(this.mousePos2.x), parseInt(this.mousePos2.y)),
                new Point2D(parseInt(this.mousePos1.x), parseInt(this.mousePos1.y))
            );
            this.ctx.putImageData(this.imageData, 0, 0);
        }

        this.mousePos2 = this.mousePos1;

        window.requestAnimationFrame(() => this.loop());
    }

    clearCanvas() {
        this.imageData.data.fill(0);
        this.ctx.putImageData(this.imageData, 0, 0);
        this.imageLayer = [...this.imageData.data];
    }

    setTool(tool) {
        // ToDo: Factory pattern
        this.toolContext.transitionTo(new this.toolContext.classMap[tool]());
    }

    setToolSize(toolSize) {
        this.toolSize = parseInt(toolSize) * 2 - 1;
    }

    setToolOpacity(toolOpacity) {
        this.lineColor.a = parseInt(toolOpacity);
    }

    putWorkspaceImage() {
        const workspaceImage = this.canvas.toDataURL('image/png');

        window.location.href = workspaceImage;
    }
}
