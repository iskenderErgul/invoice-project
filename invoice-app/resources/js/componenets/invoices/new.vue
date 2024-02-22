

<template>
    <div class="container">
        <div class="invoices">

            <div class="card__header">
                <div>
                    <h2 class="invoice__title">New Invoice</h2>
                </div>
                <div>

                </div>
            </div>

            <div class="card__content">
                <div class="card__content--header">
                    <div>
                        <p class="my-1">Customer</p>
                        <select name="" id="" class="input" v-model="form.customer_id">
                            <option disabled>Select customer</option>
                            <option :value="customer.id" v-for="customer in allCustomers" :key="customer.id">
                                {{customer.firstname}}
                            </option>
                        </select>

                    </div>
                    <div>
                        <p class="my-1">Date</p>
                        <input id="date" placeholder="dd-mm-yyyy" type="date" class="input" v-model="form.date">
                        <p class="my-1">Due Date</p>
                        <input id="due_date" type="date" class="input" v-model="form.due_date">
                    </div>
                    <div>
                        <p class="my-1">Number</p>
                        <input type="text" class="input" v-model="form.number">
                        <p class="my-1">Reference(Optional)</p>
                        <input type="text" class="input" v-model="form.reference">
                    </div>
                </div>
                <br><br>
                <div class="table">

                    <div class="table--heading2">
                        <p>Item Description</p>
                        <p>Unit Price</p>
                        <p>Qty</p>
                        <p>Total</p>
                        <p></p>
                    </div>

                    <!-- item 1 -->
                    <div class="table--items2" v-for="(itemcard,i) in listCard" :key="itemcard.id">
                        <p>{{itemcard.item_code}} {{itemcard.description}}</p>
                        <p>
                            <input type="text" class="input" v-model="itemcard.unit_price">
                        </p>
                        <p>
                            <input type="text" class="input" v-model="itemcard.quantity" >
                        </p>
                        <p v-if="itemcard.quantity">
                            $ {{ (itemcard.quantity)*(itemcard.unit_price) }}
                        </p>
                        <p v-else></p>
                        <p style="color: red; font-size: 24px;cursor: pointer;" @click="removeItem(i)">
                            &times;
                        </p>
                    </div>
                    <div style="padding: 10px 30px !important;">
                        <button class="btn btn-sm btn__open--modal" @click="openModel()">Add New Line</button>
                    </div>
                </div>

                <div class="table__footer">
                    <div class="document-footer" >
                        <p>Terms and Conditions</p>
                        <textarea cols="50" rows="7" class="textarea" v-model="form.terms_and_conditions" ></textarea>
                    </div>
                    <div>
                        <div class="table__footer--subtotal">
                            <p>Sub Total</p>
                            <span>$ {{SubTotal()}}</span>
                        </div>
                        <div class="table__footer--discount">
                            <p>Discount</p>
                            <input type="text" class="input" v-model="form.discount">
                        </div>
                        <div class="table__footer--total">
                            <p>Grand Total</p>
                            <span>$ {{Total()}}</span>
                        </div>
                    </div>
                </div>


            </div>
            <div class="card__header" style="margin-top: 40px;">
                <div>

                </div>
                <div>
                    <a class="btn btn-secondary" @click="onSave()">
                        Save
                    </a>
                </div>
            </div>

        </div>

        <div class="modal main__modal " :class="{show : showModal}">
            <div class="modal__content">
                <span class="modal__close btn__close--modal" @click="closeModel">×</span>
                <h3 class="modal__title">Add Item</h3>
                <hr><br>
                <div class="modal__items">
                    <ul style="list-style: none">
                        <li v-for="product in listProduct" :key="product.id" style="display: grid; grid-template-columns:30px 350px 15px ; align-items: center;padding-bottom: 5px ">
                            <p>{{product.id+1}}</p>
                            <a>{{product.item_code}} {{product.description}}</a>
                            <button @click="addCard(product)" style="border: 1px solid #e0e0e0; width: 35px ; height: 35px ;cursor: pointer">+</button>
                        </li>
                    </ul>

                </div>
                <br><hr>
                <div class="model__footer">
                    <button class="btn btn-light mr-2 btn__close--modal" @click="closeModel">
                        Cancel
                    </button>
                </div>
            </div>
        </div>

        <br><br><br>

    </div>
</template>

<script setup>
import {onMounted ,ref} from "vue";
import axios from "axios";
import {stringifyQuery} from "vue-router";
import router from "@/router/index.js";

let form  = ref([]);
let allCustomers = ref([]);
let customer_id  = ref([]);
let item = ref([]);
let listCard = ref([]);
let listProduct = ref([]);
const showModal = ref(false);
const hideModal = ref(true);

onMounted(async () => {
    indexForm();
    getAllCustomers();
    getAllProducts();
})
const indexForm = async () => {
    let response = await axios.get('/api/create_invoice');
    form.value = response.data;
}
const getAllCustomers = async () => {
    let  response = await  axios.get('/api/customers');
    allCustomers.value = response.data.customers;
}
const getAllProducts = async () => {
    let response = await  axios.get('/api/products');
    listProduct.value=response.data.products;
}

const addCard = (item) => {
    const itemcard = {
        id : item.id,
        item_code : item.item_code,
        description : item.description,
        unit_price : item.unit_price,
        quantity : item.quantity
    }
    listCard.value.push(itemcard);
    closeModel();
}
const openModel = () => {
    showModal.value= !showModal.value;
}
const closeModel = () => {
    showModal.value = !hideModal.value;
}
const removeItem = (item) => {
    listCard.value.splice(item,1);
}

const SubTotal = () => {
    let total = 0;
    listCard.value.map((data) => {
        total = total + (data.quantity*data.unit_price)
    });
    return total;
}
const Total = () => {
    return SubTotal()-form.value.discount;
}

const onSave = async () => {
    if (listCard.value.length >= 1) {
        let subtotal = SubTotal();
        let total = Total();

        const formData = new FormData();
        formData.append('invoice_item', JSON.stringify(listCard.value));
        formData.append('customer_id', form.value.customer_id);
        formData.append('date', form.value.date);
        formData.append('due_date', form.value.due_date);
        formData.append('number', form.value.number);
        formData.append('reference', form.value.reference);
        formData.append('discount', form.value.discount);
        formData.append('sub_total', subtotal);
        formData.append('total', total);
        formData.append('terms_and_conditions', form.value.terms_and_conditions);

        console.log(formData);

        await axios.post('/api/add_invoice', formData);
        listCard.value = [];
        router.push('/');
    }
}


</script>
