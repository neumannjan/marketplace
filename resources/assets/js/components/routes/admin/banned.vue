<template>
    <div>
        <search class="mb-4" v-model="search" @submit="requestSearch"/>
        <div class="table-responsive">
            <table class="table">
                <thead>
                <tr>
                    <th>{{ translations.username }}</th>
                    <th>{{ translations.display_name }}</th>
                    <th>{{ translations.email }}</th>
                    <th>{{ translations.profile }}</th>
                </tr>
                </thead>
                <infinite-scroll as="tbody" :busy="busy" @request="request">
                    <tr v-for="user of users" :key="user.username">
                        <th>@{{ user.username }}</th>
                        <th>{{ user.display_name }}</th>
                        <td>{{ user.email }}</td>
                        <td>
                            <router-link :title="translations.profile"
                                         :to="{name: 'user', params: {username: user.username}}">
                                <icon name="link"/>
                            </router-link>
                        </td>
                    </tr>
                    <tr v-if="nextUrl">
                        <td colspan="3" class="text-center">
                            <icon name="spinner" label="Loading" pulse/>
                        </td>
                    </tr>
                </infinite-scroll>
            </table>
        </div>
    </div>
</template>

<script lang="ts">
    import {Component, mixins, Prop, Watch} from "JS/components/class-component";
    import route from "JS/components/mixins/route";
    import routeGuard from "JS/components/mixins/route-guard";
    import store from "JS/store";
    import api from "JS/api";
    import {PaginatedResponse, User, UserStatus} from "JS/api/types";
    import {Dictionary} from "vue-router/types/router";

    import InfiniteScroll from 'JS/components/widgets/infinite-scroll.vue';
    import Search from 'JS/components/widgets/search.vue';

    import "vue-awesome/icons/spinner";
    import "vue-awesome/icons/link";

    @Component({
        name: 'admin-banned-route',
        components: {
            InfiniteScroll,
            Search
        },
    })
    export default class AdminRoute extends mixins(route, routeGuard('admin', () => store.state.is_admin)) {
        readonly isTopLevelRoute: boolean = true;

        get title(): string {
            return this.$store.getters.trans('interface.page.banned');
        }

        @Prop({type: String})
        query!: string;

        requestBusy: boolean = false;
        masonryBusy: boolean = true;
        nextUrl: string | null | false = null;
        active: boolean = true;
        users: User[] = [];

        search: string = '';

        requestSearch() {
            let params: Dictionary<string>;

            if (this.search)
                params = {query: this.search};
            else
                params = {};

            this.$router.push({
                name: 'admin-banned',
                params: params
            });
        }

        get translations() {
            return {
                username: this.$store.getters.trans('interface.form.username'),
                display_name: this.$store.getters.trans('interface.form.display_name'),
                email: this.$store.getters.trans('interface.form.email'),
                profile: this.$store.getters.trans('interface.button.profile'),
            }
        }

        get busy() {
            return this.requestBusy || this.masonryBusy;
        }

        request(to?: string, params?: { [index: string]: any }) {
            if (this.active === false || this.requestBusy === true)
                return;

            const endpoint = to ? to : this.nextUrl;

            if (!endpoint)
                return;

            this.requestBusy = true;

            type R = PaginatedResponse<User[]>;

            const promise = params ? api.requestSingle<R>(endpoint, params) : api.requestByURL<R>(endpoint);

            promise.then(result => {
                this.addItems(result.data);
                this.nextUrl = result['next_page_url'];
                this.requestBusy = false;
                this.masonryBusy = true;
            });
        }

        requestNew() {
            this.users = [];
            if (this.query) {
                this.search = this.query;
                this.request('user-search', {
                    query: this.query,
                    status: UserStatus.Banned
                });
            } else {
                this.request("/api/users?scope=banned");
            }
        }

        addItems(users: User[]) {
            this.users = [...this.users, ...users];
        }

        created() {
            this.requestNew();
        }

        @Watch('query')
        onQueryChanged() {
            this.requestNew();
        }

        activated() {
            this.active = true;
        }

        deactivated() {
            this.active = false;
        }
    }
</script>