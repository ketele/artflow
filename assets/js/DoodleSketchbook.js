import {Doodle} from './doodle/Doodle';
import {Point2D} from './sketchbook/Point2D';
import {Utils} from './Utils';
import {Workspace} from './sketchbook/Workspace';

// ToDo: Think about Doodle js refactoring
class DoodleSketchbook {
    loadDoodle(type, coordinates) {
        if (typeof type === 'undefined' || type === null) {
            if (typeof coordinates !== 'undefined' && coordinates !== null && coordinates !== '') {
                type = 'definedNodes';
            } else {
                type = 'unbalancedNodes';
            }
        }

        this.canvas = document.getElementById('doodle-sketchbook');
        this.ctx = this.canvas.getContext('2d');

        this.size = 600;

        if (this.canvas.parentElement.offsetWidth < this.size) {
            this.size = this.canvas.parentElement.offsetWidth;
        }

        this.ctx.canvas.width = this.size;
        this.ctx.canvas.height = this.size;

        this.doodle = new Doodle(new Point2D(this.canvas.offsetWidth / 2, this.canvas.offsetHeight / 2), this.canvas.offsetHeight / 4);
        this.doodle.width = this.size;
        this.doodle.height = this.size;

        // ToDo: define canvas size holding in mind that after diving shape canvas, and sb canvas have to be the same
        this.imageData = this.ctx.getImageData(0, 0, this.canvas.offsetWidth / 2 * 2, this.canvas.offsetHeight / 2 * 2);
        this.data = this.imageData.data;
        this.imageData.data.fill(255);
        this.ctx.putImageData(this.imageData, 0, 0);

        if (type === 'definedNodes') {
            this.doodle.setNodes(coordinates);
        } else if (type === 'symmetricalNodes') {
            this.doodle.generateSymmetricalNodes();
        } else {
            this.doodle.generateUnbalancedNodes();
        }

        this.doodle.draw(this.ctx);
        this.imageData = this.ctx.getImageData(0, 0, this.canvas.offsetWidth, this.canvas.offsetHeight);
        this.data = this.imageData.data;
    }

    loadSketchbook() {
        this.canvas_sketchbook = document.getElementById('sketchbook');
        this.workspace = new Workspace(this.canvas_sketchbook);
        this.workspace.run();
    }

    getImageFile(imgDataArray = []) {
        const tempCanvas = document.createElement('canvas');
        tempCanvas.width = this.workspace.width;
        tempCanvas.height = this.workspace.height;
        const ctx = tempCanvas.getContext('2d');

        const imageData = ctx.getImageData(0, 0, this.workspace.width, this.workspace.height);
        const data = imageData.data;

        const flattenCanvases = new Promise((resolve, reject) => {
            imgDataArray.forEach((imgData) => {
                data.forEach((d, i) => {
                    data[i] = d + imgData[i];
                });
            });

            resolve(data);
        });

        return flattenCanvases.then((flattenData) => {
            for (let i = 0; i < flattenData.length * 4; i += 4) {
                if (this.workspace.data[i + 3] === 255) {
                    flattenData[i] = this.workspace.data[i];
                    flattenData[i + 1] = this.workspace.data[i + 1];
                    flattenData[i + 2] = this.workspace.data[i + 2];
                    flattenData[i + 3] = this.workspace.data[i + 3];
                } else if (this.workspace.data[i + 3] !== 0) {
                    const maxOpacity = (flattenData[i + 3] + this.workspace.data[i + 3] <= 255)
                        ? flattenData[i + 3] + this.workspace.data[i + 3]
                        : 255;
                    const topWage = (this.workspace.data[i + 3] / maxOpacity);
                    const baseWage = 1 - topWage;

                    flattenData[i] = ((baseWage * flattenData[i]) + (topWage * this.workspace.data[i]));
                    flattenData[i + 1] = ((baseWage * flattenData[i + 1]) + (topWage * this.workspace.data[i + 1]));
                    flattenData[i + 2] = ((baseWage * flattenData[i + 2]) + (topWage * this.workspace.data[i + 2]));
                    flattenData[i + 3] = flattenData[i + 3] + this.workspace.data[i + 3];
                }
            }

            ctx.putImageData(imageData, 0, 0);

            return tempCanvas.toDataURL('image/png');
        });
    }

    saveImageToTemp() {
        const sourceDoodleId = document.getElementById('id').value;
        let sourceDoodle = JSON.stringify({
            size: this.size,
            doodle: this.doodle.curves
        });

        sourceDoodle = encodeURIComponent(sourceDoodle);

        Utils.showLoadingOverlay();
        const getImageFile = new Promise((resolve, reject) => {
            const image = this.getImageFile([this.data]);
            resolve(image);
        });

        getImageFile.then((image) => {
            fetch('/api/store_doodle_temp', {
                method: 'POST',
                body: 'imgBase64=' + image,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Content-type': 'application/x-www-form-urlencoded'
                }
            }).then(response => response.json().then(data => {
                if (response.status < 300) {
                    const tempForm = document.createElement('form');
                    tempForm.method = 'POST';
                    tempForm.target = '_blank';
                    tempForm.action = `/${Utils.getUrlParam(0)}/add_doodle`;
                    tempForm.setAttribute('name', 'doodle');
                    tempForm.innerHTML = `
                        <input type="text" name="tempDir" value="${data.tempDir}" />
                        <input type="text" name="sourceDoodle" value="${sourceDoodle}" />
                        <input type="text" name="sourceDoodleId" value="${sourceDoodleId}" />
                    `;

                    const formObj = document.body.appendChild(tempForm);
                    tempForm.submit();
                    formObj.remove();
                }

                Utils.hideLoadingOverlay();
            }));
        });
    }
}

Utils.ready(() => {
    let coordinatesJson = document.getElementById('coordinates').value;
    coordinatesJson = (typeof coordinatesJson !== 'undefined' && coordinatesJson !== '') ? JSON.parse(coordinatesJson) : null;

    const doodleSketchbook = new DoodleSketchbook();
    doodleSketchbook.loadDoodle(null, coordinatesJson);
    doodleSketchbook.loadSketchbook();

    document.getElementById('refresh-doodle').addEventListener('click', e => {
        document.getElementById('coordinates').value = '';
        document.getElementById('id').value = '';
        doodleSketchbook.doodle.clearCanvas(doodleSketchbook.ctx);
        doodleSketchbook.loadDoodle('unbalancedNodes');
    });

    document.getElementById('refresh-symmetrical-doodle').addEventListener('click', e => {
        document.getElementById('coordinates').value = '';
        document.getElementById('id').value = '';
        doodleSketchbook.doodle.clearCanvas(doodleSketchbook.ctx);
        doodleSketchbook.loadDoodle('symmetricalNodes');
    });

    document.getElementById('clear-sketchbook').addEventListener('click', e => {
        doodleSketchbook.workspace.clearCanvas();
    });

    document.getElementById('save-image').addEventListener('click', e => {
        doodleSketchbook.saveImageToTemp();
    });

    document.getElementById('pencil').addEventListener('click', e => {
        doodleSketchbook.workspace.setTool('Pencil');
    });

    document.getElementById('eraser').addEventListener('click', e => {
        doodleSketchbook.workspace.setTool('Eraser');
    });

    document.getElementById('opacity').addEventListener('change', e => {
        doodleSketchbook.workspace.setToolOpacity(e.target.value);
    });

    document.getElementById('size').addEventListener('change', e => {
        doodleSketchbook.workspace.setToolSize(e.target.value);
    });
});
