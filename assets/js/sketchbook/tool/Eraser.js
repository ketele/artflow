import {Tool} from './Tool';

export class Eraser extends Tool {
    use(a, b) {
        return a - b;
    }
}
