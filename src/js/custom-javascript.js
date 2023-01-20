function setNavBgPosition() {
    let root = document.documentElement;
    root.style.setProperty('--nav-bg-position', (Math.random() * 2000) + 'px');
}

setNavBgPosition();
