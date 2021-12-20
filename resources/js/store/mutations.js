let mutations = {
    SET (state, [variable, value]) {
        state[variable] = value
    },
    TOGGLE_SIDEBAR_DESKTOP (state) {
        const sidebarOpened = [true, 'responsive'].includes(state.sidebarShow)
        state.sidebarShow = sidebarOpened ? false : 'responsive'
    },
    TOGGLE_SIDEBAR_MOBILE (state) {
        const sidebarClosed = [false, 'responsive'].includes(state.sidebarShow)
        state.sidebarShow = sidebarClosed ? true : 'responsive'
    },
    RESET(state){
        state.user = {};
        localStorage.clear()
        delete axios.defaults.headers.common['Authorization'];
    }
}
export default mutations