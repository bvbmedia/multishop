function CONFIRM(label) {
    if (confirm(label)) {
        return true;
    } else {
        return false;
    }
}
function isMobile() {
    try{ document.createEvent("TouchEvent"); return true; }
    catch(e){ return false; }
}