<script setup>
import { Link } from '@inertiajs/vue3';
import { ref } from 'vue';

const showDrawer = ref(false);

function toggleDrawer() {
    showDrawer.value = !showDrawer.value;
}
</script>

<template>
    <v-app>
        <v-app-bar elevation="1" color="blue" class="pe-2">
            <v-app-bar-title>
                <v-icon icon="mdi-menu" @click="toggleDrawer"></v-icon>
                <span class="ms-2">Inventory</span>
            </v-app-bar-title>
            <template #append>
                <!-- <v-avatar id="profile-icon" size="2.7rem" class="cursor-pointer"> -->
                <v-icon id="profile-icon" class="cursor-pointer" icon="mdi-account" size="x-large" />
                <!-- </v-avatar> -->
                <v-menu activator="#profile-icon">
                    <v-card>
                        <v-card-title>Muhammad Junaidi</v-card-title>
                        <v-card-subtitle>IT</v-card-subtitle>
                        <v-divider />
                        <v-list density="comfortable">
                            <v-list-item value="">profile</v-list-item>
                            <Link :href="route('logout')">
                            <v-list-item value="">log out</v-list-item>
                            </Link>
                        </v-list>
                    </v-card>
                </v-menu>
            </template>
        </v-app-bar>
        <v-navigation-drawer v-model="showDrawer">
            <v-list color="blue">
                <Link :href="route('home')">
                <v-list-item value>
                    <div class="flex items-baseline gap-1">
                        <v-icon icon="mdi-home" />
                        <div>Home</div>
                    </div>
                </v-list-item>
                </Link>
                <Link href="#">
                <v-list-item value="masuk">
                    <div class="flex items-baseline gap-1">
                        <v-icon icon="mdi-card-plus" />
                        <div>Barang Masuk</div>
                    </div>
                </v-list-item>
                </Link>
                <v-list-item value="keluar">
                    <div class="flex items-baseline gap-1">
                        <v-icon icon="mdi-card-minus" />
                        <div>Barang Keluar</div>
                    </div>
                </v-list-item>
                <v-list-group>
                    <template #activator="{ props }">
                        <v-list-item :="props">
                            <v-list-item-title>
                                <div class="flex items-baseline gap-1">
                                    <v-icon icon="mdi-file" />Master
                                </div>
                            </v-list-item-title>
                        </v-list-item>
                    </template>
                    <v-list-item value="1">
                        <div class="flex items-baseline gap-1"><v-icon icon="mdi-tag" /><span>Jenis Barang</span></div>
                    </v-list-item>
                    <v-list-item value="2">
                        <div class="flex items-baseline gap-1"><v-icon icon="mdi-face-agent" /><span>Suplier</span>
                        </div>
                    </v-list-item>
                    <Link href="#">
                    <v-list-item value="3">
                        <div class="flex items-baseline gap-1"><v-icon icon="mdi-account-group" /><span>Akun</span>
                        </div>
                    </v-list-item>
                    </Link>
                </v-list-group>
                <Link href="#">
                <v-list-item value="settings">
                    <v-icon icon="mdi-cog"></v-icon> Setting
                </v-list-item>
                </Link>
            </v-list>
        </v-navigation-drawer>
        <v-main>
            <slot />
        </v-main>

        <v-footer app color="secondary" class="static! max-h-12 self-end">
            <div class="w-full! text-center">©{{ new Date().getFullYear() }} Made Barely With My Hand<v-icon
                    icon="mdi-hand-peace" /></div>
        </v-footer>
    </v-app>
</template>