<template>
    <card v-bind="cardData" :class="[color('border-')]">
        <router-link :to="toAuthor" v-if="showAuthor" slot="header" class="offer-card-header text-dark">
            <img v-if="profileImage" class="profile-img rounded-circle"
                 :src="profileImage['1x']"
                 :srcset="`${profileImage['1x']}, ${profileImage['2x']} 2x`"/>
            <div v-else="" class="profile-img profile-img-placeholder">
                <icon name="user-circle" scale="2"/>
            </div>
            <span class="ml-2 author-info">
                {{ data.author.display_name }} <small class="text-muted">{{ `@${data.author.username}` }}</small>
            </span>
        </router-link>

        <a href="#" :class="[color('text-', 'dark')]">
            <h4 class="card-title">{{ data.name }}
                <badge v-for="(badge, index) in badges" :key="index" v-bind="badge"/>
            </h4>
        </a>
        <p class="card-text">{{ shortDesc }}</p>
        <p class="h5 card-text">{{ data.price }}</p>

        <card-icon-footer slot="footer" :buttons="buttons" :gray="true"/>
    </card>
</template>

<script>
    import Card from "../../card";
    import CardIconFooter from "../../card-icon-footer";
    import Badge from 'JS/components/widgets/badge'

    import 'vue-awesome/icons/heart';
    import 'vue-awesome/icons/shopping-cart';
    import 'vue-awesome/icons/expand';
    import 'vue-awesome/icons/user-circle';

    export default {
        name: "offer-card",
        components: {
            Card,
            CardIconFooter,
            Badge
        },
        data: () => ({
            buttons: [
                {
                    icon: 'heart',
                    label: 'Like',
                    callback: null
                },
                {
                    icon: 'shopping-cart',
                    label: 'Buy',
                    callback: null
                },
                {
                    icon: 'expand',
                    label: 'Expand',
                    callback: null
                }
            ]
        }),
        props: {
            data: {
                type: Object,
                required: true
            },
            showAuthor: {
                type: Boolean,
                default: true,
            }
        },
        methods: {
            color(prefix = '', defaultColor = null) {
                const doReturn = (value = defaultColor) => {
                    if (!value)
                        return undefined;
                    return prefix + value;
                };

                if (!this.data)
                    return doReturn();

                let badges = [];

                switch (this.data.status) {
                    case 0: // draft
                        return doReturn('warning');
                }

                if (this.data.expired)
                    return doReturn('danger');

                return doReturn();
            }
        },
        computed: {
            cardData() {
                let image = this.data.images[0];

                return {
                    key: this.data.id,
                    alt: this.data.name,
                    img: image['size_original'],
                    thumb: image['size_tiny'],
                    width: image['width'],
                    height: image['height'],
                };
            },
            shortDesc() {
                let desc = this.data.description.substr(0, 300);
                desc = desc.substr(0, Math.min(desc.length, desc.lastIndexOf(" ")));

                if (desc)
                    desc += '...';
                return desc;
            },
            profileImage() {
                if (this.data.author.profile_image) {
                    return {
                        '1x': this.data.author.profile_image.size_icon,
                        '2x': this.data.author.profile_image.size_icon_2x,
                    }
                }

                return null;
            },
            toAuthor() {
                return {
                    name: 'user',
                    params: {
                        username: this.data.author.username
                    }
                }
            },
            badges() {
                // TODO translate

                if (!this.data)
                    return null;

                let badges = [];

                switch (this.data.status) {
                    case 0:
                        badges.push({message: 'Draft', type: 'warning'});
                        break;
                    case 2:
                        badges.push({message: 'Sold', type: 'info'});
                        break;
                }

                if (this.data.expired)
                    badges.push({message: 'Expired', type: 'danger'});

                return badges;
            }
        }
    }
</script>

<style scoped lang="scss" type="text/scss">
    @import '~CSS/includes';

    a {
        text-decoration: none;
    }

    .offer-card-header {
        line-height: 1em;
        display: flex;
        align-items: center;
    }

    .profile-img {
        display: block;
        float: left;
        min-width: 32px;
        height: 32px;
    }

    .profile-img-placeholder {
        color: $gray-400;
    }

    .author-info {
        word-wrap: normal;
        overflow: hidden;
        text-overflow: ellipsis;
    }
</style>