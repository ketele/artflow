import {Pencil} from './Pencil';
import {Eraser} from './Eraser';

export class ToolContext {
    constructor(tool = new Pencil()) {
        this.transitionTo(tool);
        this.classMap = {
            Pencil: Pencil,
            Eraser: Eraser
        };
    }

    transitionTo(tool) {
        this.tool = tool;
        this.tool.setContext(this);
    }

    use(a, b) {
        return this.tool.use(a, b);
    }
}
