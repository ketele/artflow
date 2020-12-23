import {Doodle} from "./doodle/doodle";
import {Workspace} from "./sketchbook/workspace";
import {Utils} from "./utils";

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
    loadDoodle(type, coordinates){
        if( typeof type === "undefined" || type === null ){
            if( typeof coordinates !== "undefined" && coordinates !== null && coordinates !== "" )
                type = 'definedNodes';
            else
                type = 'unbalancedNodes';
        }

        this.canvas = document.getElementById('doodle-sketchbook');
        this.ctx = this.canvas.getContext('2d');

        this.size = 600;

        if( this.canvas.parentElement.offsetWidth < this.size )
            this.size = this.canvas.parentElement.offsetWidth;

        this.ctx.canvas.width  = this.size;
        this.ctx.canvas.height = this.size;

        this.doodle = new Doodle( this.canvas.offsetWidth/2, this.canvas.offsetHeight/2, this.canvas.offsetHeight/4 );
        this.doodle.setwWidth(this.size);
        this.doodle.setwHeight(this.size);

        this.imageData = this.ctx.getImageData(0, 0, this.canvas.offsetWidth, this.canvas.offsetHeight);
        this.data = this.imageData.data;
        this.imageData.data.fill(255);
        this.ctx.putImageData(this.imageData, 0, 0);

        if( type === "definedNodes" )
            this.doodle.setNodes( coordinates );
        else if ( type === "symmetricalNodes" )
            this.doodle.generateSymmetricalNodes();
        else
            this.doodle.generateUnbalancedNodes();

        this.doodle.draw(this.ctx);
        this.imageData = this.ctx.getImageData(0, 0, this.canvas.offsetWidth, this.canvas.offsetHeight);
        this.data = this.imageData.data;
    }

    loadSketchbook(){
        this.canvas_sketchbook = document.getElementById('sketchbook');
        this.workspace = new Workspace(this.canvas_sketchbook);
        this.workspace.run();

    }

    getImageFile(img_data_array = []){
        let temp_canvas = document.createElement("canvas");
        temp_canvas.width  = this.workspace.width;
        temp_canvas.height = this.workspace.height;
        let ctx = temp_canvas.getContext('2d');

        let imageData = ctx.getImageData(0, 0, this.workspace.width, this.workspace.height);
        let data = imageData.data;

        const flatten_canvases = new Promise((resolve, reject) => {
            img_data_array.forEach((img_data) => {
                data.forEach((d, i) => {
                    data[i] = d + img_data[i];
                });
            });

            resolve(data);
        });

        return flatten_canvases.then( (flatten_data) => {
            for( let i = 0; i < flatten_data.length * 4; i += 4 ) {
                if( this.workspace.data[i + 3] === 255 ) {
                    flatten_data[i] = this.workspace.data[i];
                    flatten_data[i + 1] = this.workspace.data[i + 1];
                    flatten_data[i + 2] = this.workspace.data[i + 2];
                    flatten_data[i + 3] = this.workspace.data[i + 3];
                }else if ( this.workspace.data[i + 3] === 0 ){

                } else {
                    let max_opacity = ( flatten_data[i + 3] + this.workspace.data[i + 3] <= 255 )
                        ? flatten_data[i + 3] + this.workspace.data[i + 3] : 255;
                    let top_wage = ( this.workspace.data[i + 3] / max_opacity );
                    let base_wage = 1 - top_wage;
                    flatten_data[i] =       (( base_wage * flatten_data[i] )     + ( top_wage * this.workspace.data[i] ));
                    flatten_data[i + 1] =   (( base_wage * flatten_data[i + 1] ) + ( top_wage * this.workspace.data[i + 1]));
                    flatten_data[i + 2] =   (( base_wage * flatten_data[i + 2] ) + ( top_wage * this.workspace.data[i + 2]));
                    flatten_data[i + 3] =   flatten_data[i + 3] + this.workspace.data[i + 3];
                }
            }

            ctx.putImageData(imageData, 0, 0);

            return temp_canvas.toDataURL("image/png");
        });
    }

    putImage(){
        const get_image_file = new Promise((resolve, reject) => {
            let image = this.getImageFile([this.data]);
            resolve(image);
        });

        get_image_file.then((image)=>{
            window.location.href = image;
        });
    }

    saveImageToTemp(){
        let source_doodle_id = document.getElementById('id').value;
        let source_doodle = JSON.stringify({
            'size': this.size,
            'doodle': this.doodle.curves
        });

        source_doodle = encodeURIComponent(source_doodle);
        //console.log(source_doodle);

        Utils.showLoadingOverlay();
        const get_image_file = new Promise((resolve, reject) => {
            let image = this.getImageFile([this.data]);
            resolve(image);
        });

        get_image_file.then((image)=>{
            const xhr = new XMLHttpRequest();

            xhr.onreadystatechange = e => {
                if (xhr.readyState === 4 && xhr.status === 200) {
                    let response = JSON.parse(xhr.response);

                    const temp_form = document.createElement('form');
                    temp_form.method = "POST";
                    temp_form.target = "_blank";
                    temp_form.action = "/" + Utils.getUrlParam(0) + "/add_doodle";
                    temp_form.setAttribute('name', 'doodle');
                    temp_form.innerHTML = `
<input type="text" name="temp_dir" value="${response.temp_dir}" />
<input type="text" name="source_doodle" value="${source_doodle}" />
<input type="text" name="source_doodle_id" value="${source_doodle_id}" />
`;
                    let formObj = document.body.appendChild(temp_form);
                    temp_form.submit();
                    formObj.remove();

                    Utils.hideLoadingOverlay();
                }
            };
            xhr.open("POST", "/store_doodle_temp_ajax", true);
            xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
            xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
            xhr.send('imgBase64=' + image);
        });
    }
}

window.addEventListener('load', e => {
    let coordinatesJson = document.getElementById('coordinates').value;
    coordinatesJson = ( typeof coordinatesJson !== "undefined" && coordinatesJson !== "" ) ? JSON.parse(coordinatesJson) : null;

    let doodle_sketchbook = new DoodleSketchbook();
    doodle_sketchbook.loadDoodle(null, coordinatesJson);
    doodle_sketchbook.loadSketchbook();

    document.getElementById('refresh-doodle').addEventListener('click', e => {
        document.getElementById('coordinates').value = "";
        document.getElementById('id').value = "";
        doodle_sketchbook.doodle.clearCanvas(doodle_sketchbook.ctx);
        doodle_sketchbook.loadDoodle("unbalancedNodes");
    } );

    document.getElementById('refresh-symmetrical-doodle').addEventListener('click', e => {
        document.getElementById('coordinates').value = "";
        document.getElementById('id').value = "";
        doodle_sketchbook.doodle.clearCanvas(doodle_sketchbook.ctx);
        doodle_sketchbook.loadDoodle("symmetricalNodes");
    } );

    document.getElementById('clear-sketchbook').addEventListener('click', e => {
       doodle_sketchbook.workspace.clearCanvas();
    } );

    /*document.getElementById('download-image').addEventListener('click', e => {
       doodle_sketchbook.putImage();
    } );*/

    document.getElementById('save-image').addEventListener('click', e => {
       doodle_sketchbook.saveImageToTemp();
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