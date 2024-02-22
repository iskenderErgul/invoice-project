import {createRouter, createWebHashHistory} from "vue-router";
import invoiceIndex from '../componenets/invoices/index.vue';
import notFound from '../componenets/invoices/NotFound.vue';
import invoiceNew from '../componenets/invoices/new.vue'
import invoiceShow from '../componenets/invoices/show.vue'
import invoiceEdit from '../componenets/invoices/edit.vue';

const routes = [
    {
        path : '/',
        component :invoiceIndex
    },
    {
        path:  '/:pathMatch(.*)*',
        component: notFound
    },
    {
        path: '/invoice/new',
        component: invoiceNew
    },
    {
        path: '/invoice/show/:id',
        component: invoiceShow,
        props : true,
    },
    {
        path:'/invoice/edit/:id',
        component: invoiceEdit,
        props: true
    }


]


const router = createRouter({
    history: createWebHashHistory(),
    routes
})

export default router
