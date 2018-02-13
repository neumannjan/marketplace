<template>
    <div class="card">
        <div class="card-header">
            <div class="d-flex flex-row align-items-center">
                <template v-if="user">
                    <button class="btn btn-link btn-link-gray btn-icon mr-1" @click="user = null">
                        <icon name="arrow-left" label="back"/>
                    </button>
                    <router-link :to="{name: 'user', params: {username: user.username}}"
                                 class="d-flex flex-row align-items-center no-decoration">
                        <profile-img :img="user.profile_image ? user.profile_image : {}" :img-size="imgSize"
                                     class="mr-2" :style="{margin: `-${imgSize/2}px 0`}"/>
                        <h1 class="h6 m-0 text-dark">{{ user.display_name }}</h1>
                    </router-link>
                </template>
                <template v-else>
                    <h1 class="h6 m-0">Chat</h1>
                </template>
                <button class="btn btn-link btn-link-gray btn-icon ml-auto" @click="onClose">
                    <icon name="close" label="close"/>
                </button>
            </div>
        </div>
        <div class="overflow-scroll-y p-2">
            <list-messages v-if="user" :user="user" :img-size="imgSize"
                           :indicator-size="indicatorSize"
                           :posted-messages="posted"/>
            <list-conversations v-else @select="onSelectUser" :img-size="imgSize"/>
        </div>

        <div v-if="user" class="p-1 mt-auto">
            <form @submit.prevent="sendMessage" class="input-group input-group-sm">
                <input type="text" class="form-control" placeholder="Type a message" v-model="message">
                <div class="input-group-append">
                    <button class="btn btn-outline-primary" type="submit">Send</button>
                </div>
            </form>
        </div>
    </div>
</template>

<script>
    import api from 'JS/api';

    import "vue-awesome/icons/arrow-left";
    import "vue-awesome/icons/refresh";
    import "vue-awesome/icons/check-circle-o";
    import "vue-awesome/icons/check-circle";
    import "vue-awesome/icons/circle-o";
    import "vue-awesome/icons/close";
    import ListConversations from "JS/components/widgets/chat/list-conversations";
    import ListMessages from "JS/components/widgets/chat/list-messages";
    import ProfileImg from "JS/components/widgets/image/profile-img";

    export default {
        components: {
            ListMessages,
            ListConversations,
            ProfileImg
        },
        name: 'chat-card',
        props: {
            imgSize: {
                type: Number,
                default: 32
            },
            indicatorSize: {
                type: Number,
                default: 14
            }
        },
        data: () => ({
            user: null,
            message: '',
            posted: []
        }),
        methods: {
            async sendMessage() {
                if (this.message) {
                    try {
                        const message = await api.requestSingle('message-send', {
                            to: this.user.username,
                            content: this.message
                        });

                        this.posted = [message];
                        this.message = '';
                    } catch (e) {
                    }
                }
            },
            onClose() {
                this.$emit('close');
            },
            onSelectUser(user) {
                this.user = user;
                this.posted = [];
            }
        }
    }
</script>