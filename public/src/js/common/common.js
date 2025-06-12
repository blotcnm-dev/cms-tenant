import { setViewHeight } from "./viewHeight.js";
import { customSelectHandler } from "../components/select.js";
import { toastLayerHandler } from "../components/toastLayer.js";

import {gnbHandler} from "../navigation/gnbClassController.js";

document.addEventListener("DOMContentLoaded", () => {
    gnbHandler();
    setViewHeight();
    window.toastLayerHandler = toastLayerHandler;

    document.body.addEventListener('click', (e)=> {
        customSelectHandler(e);
    })
})
