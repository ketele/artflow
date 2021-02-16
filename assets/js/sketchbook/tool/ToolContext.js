import {Pencil} from './Pencil';

export class ToolContext {
    constructor(tool = new Pencil()) {
        this.transitionTo(tool);
    }

    transitionTo(tool) {
        this.tool = tool;
        this.tool.setContext(this);
    }

    use(a, b) {
        return this.tool.use(a, b);
    }
}
