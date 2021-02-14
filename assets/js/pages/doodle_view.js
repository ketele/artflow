import {Utils} from '../utils';
import {DoodleManager} from '../doodle/doodle_manager';

Utils.ready(function () {
    const doodleManager = new DoodleManager();
    doodleManager.bindUIEvents();
});
