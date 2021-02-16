import {Utils} from '../Utils';
import {DoodleManager} from '../doodle/DoodleManager';

Utils.ready(function () {
    const doodleManager = new DoodleManager();
    doodleManager.bindUIEvents();
});
