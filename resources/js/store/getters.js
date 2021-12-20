let getters = {
    user: state => {
        return state.user;
    },
    claim: state => {
        return state.claim;
    },
    warranty: state => {
        return state.warranty;
    },
    motor: state => {
        return state.motor;
    },
    sidebarShow: state => {
        return state.sidebarShow;
    },
    sidebarMinimize: state => {
        return state.sidebarMinimize;
    },
    loading: state => {
        return state.loading;
    },
}
export default getters
