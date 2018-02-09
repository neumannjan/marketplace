<template>
    <div class="card">
        <div class="card-header">
            <div class="d-flex flex-row align-items-center">
                <template v-if="user">
                    <button class="btn btn-link btn-link-gray btn-icon mr-1" @click="user = null">
                        <icon name="arrow-left" label="back"/>
                    </button>
                    <a href="#" @click.prevent="" class="d-flex flex-row align-items-center no-decoration">
                        <profile-img :img="{}" :img-size="imgSize" class="mr-2"
                                     :style="{margin: `-${imgSize/2}px 0`}"/>
                        <h1 class="h6 m-0 text-dark">User name</h1>
                    </a>
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
            <div v-if="user" class="d-flex flex-column-reverse">
                <template v-for="n in 30">
                    <div v-if="n % 3" class="chat-item-right mb-3 d-flex flex-row-reverse align-items-start">
                        <div class="align-self-stretch d-flex flex-column justify-content-between align-items-start">
                            <a href="#" @click.prevent="" class="mx-2">
                                <profile-img :img="{}" :img-size="imgSize"/>
                            </a>
                            <!-- TODO label -->
                            <icon v-if="n % 5 === 0" name="check-circle" scale="0.8" class="mt-auto mx-2 text-primary"/>
                            <icon v-else-if="n % 4 === 0" name="check-circle-o" scale="0.8"
                                  class="mt-auto mx-2 text-primary"/>
                            <icon v-else-if="n % 2 === 0" name="circle-o" scale="0.8"
                                  class="mt-auto mx-2 text-primary"/>
                        </div>
                        <div class="card text-white bg-primary px-3 py-2" :style="{borderRadius: `${imgSize/2}px`}">
                            Hello
                            <template v-if="n % 2" v-for="o in 10">hello hello</template>
                        </div>
                    </div>
                    <div v-else class="chat-item-left mb-3 d-flex flex-row align-items-start">
                        <a href="#" @click.prevent="" class="mx-2">
                            <profile-img :img="{}" :img-size="imgSize"/>
                        </a>
                        <div class="card bg-light px-3 py-2" :style="{borderRadius: `${imgSize/2}px`}">
                            Hello
                            <template v-if="n % 2" v-for="o in 10">hello hello</template>
                        </div>
                    </div>
                </template>
            </div>

            <ul v-else class="list-group list-group-flush">
                <li v-for="n in 20" class="list-group-item px-2">
                    <a href="#" @click.prevent="user = {}" class="chat-user no-decoration">
                        <profile-img :img="{}" :img-size="imgSize" class="mr-2"/>
                        <div class="d-flex flex-column chat-user-content">
                            <span class="text-truncate d-block">User name</span>
                            <small class="text-truncate d-block text-muted">
                                Text
                                <template v-for="n in 10">text</template>
                            </small>
                        </div>
                    </a>
                </li>
            </ul>
        </div>

        <div v-if="user" class="p-1">
            <form @submit.prevent="" class="input-group input-group-sm">
                <input type="text" class="form-control" placeholder="Type a message">
                <div class="input-group-append">
                    <button class="btn btn-outline-primary" type="submit">Send</button>
                </div>
            </form>
        </div>
    </div>
</template>

<script>
    import ProfileImg from "JS/components/widgets/image/profile-img";

    import "vue-awesome/icons/arrow-left";
    import "vue-awesome/icons/refresh";
    import "vue-awesome/icons/check-circle-o";
    import "vue-awesome/icons/check-circle";
    import "vue-awesome/icons/circle-o";
    import "vue-awesome/icons/close";

    export default {
        components: {ProfileImg},
        name: 'chat-card',
        props: {
            imgSize: {
                type: Number,
                default: 32
            }
        },
        data: () => ({
            user: null,
        }),
        methods: {
            onClose() {
                this.$emit('close');
            }
        }
    }
</script>

<style scoped lang="scss" type="text/scss">
    @import "~CSS/includes";

    .chat-user {
        position: relative;
        display: flex;
    }

    .chat-user-content {
        line-height: 100%;
        overflow: hidden;
    }

    $chat-item-margin: map_get($spacers, 5);
    $image-margin: map_get($spacers, 2);

    .chat-item-left {
        margin-right: #{$chat-item-margin};
        margin-left: #{-1 * $image-margin};
    }

    .chat-item-right {
        margin-left: #{$chat-item-margin};
        margin-right: #{-1 * $image-margin};
    }

    .chat-item-message {

    }
</style>