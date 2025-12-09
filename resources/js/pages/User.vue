<script setup>
import ApplicationLayout from '@/layouts/ApplicationLayout.vue';
import { onMounted, ref, computed } from 'vue';
import { Head, usePage } from '@inertiajs/vue3';
import axios from '@/axios.js';

defineOptions({
    layout: ApplicationLayout
});

const page = usePage();
const currentUser = computed(() => page.props.auth.user);

const userAddForm = ref({
    name: null,
    password: null,
    username: null,
    role: null,
});
const userAddFormRef = ref(null);
const disableUsetAddSubmit = ref(false);
const showUserAddDialog = ref(false);

// Edit user states
const userEditForm = ref({
    id: null,
    name: null,
    password: null,
    username: null,
    role: null,
});
const userEditFormRef = ref(null);
const disableUserEditSubmit = ref(false);
const showUserEditDialog = ref(false);

const users = ref([]);
const roles = ref([]);
const showPassword = ref(false);
const showEditPassword = ref(false);

// Pagination states
const totalItems = ref(0);
const itemsPerPage = ref(10);
const currentPage = ref(1);
const loading = ref(false);

const userAddFormRule = {
    name: [
        requiredRule,
        (value) => value.length >= 4 || "Nama harus setidaknya 4 karakter",
        (value) => value.length < 255 || "Nama tidak boleh lebih 255 karakter",
    ],
    username: [
        requiredRule,
        (value) => value.length >= 4 || "Username harus setidaknya 4 karakter",
        (value) => value.length < 255 || "Username tidak boleh lebih 255 karakter",
        (value) => !/^[0-9._]/.test(value) || "Username hanya boleh diawali huruf",
        (value) => /^[A-Za-z0-9._]+$/.test(value) || "Username hanya boleh huruf, nomer, titik dan _",
    ],
    password: [
        requiredRule,
        (value) => value.length >= 4 || "Password harus setidaknya 4 karakter",
        (value) => value.length < 60 || "Password tidak boleh lebih 60 karakter",
    ],
    role: [
        requiredRule,
    ],
};

const userEditFormRule = {
    name: [
        requiredRule,
        (value) => value.length >= 4 || "Nama harus setidaknya 4 karakter",
        (value) => value.length < 255 || "Nama tidak boleh lebih 255 karakter",
    ],
    username: [
        requiredRule,
        (value) => value.length >= 4 || "Username harus setidaknya 4 karakter",
        (value) => value.length < 255 || "Username tidak boleh lebih 255 karakter",
        (value) => !/^[0-9._]/.test(value) || "Username hanya boleh diawali huruf",
        (value) => /^[A-Za-z0-9._]+$/.test(value) || "Username hanya boleh huruf, nomer, titik dan _",
    ],
    password: [
        // Password is optional for edit
        (value) => !value || value.length >= 4 || "Password harus setidaknya 4 karakter",
        (value) => !value || value.length < 60 || "Password tidak boleh lebih 60 karakter",
    ],
    role: [
        requiredRule,
    ],
};

function requiredRule(value) {
    return (!!value) || "Harus diisi";
}

async function fetchUsers(page, perPage) {
    loading.value = true;
    try {
        const response = await axios.get(`/api/users?page=${page}&per_page=${perPage}`);
        if (response.status === 404) {
            loading.value = false;
            return { data: [], total: 0 };
        }
        const data = response.data.data || [];
        const total = response.data.total || data.length;
        
        totalItems.value = total;
        
        const transformedData = data.map((u, i) => ({
            no: (page - 1) * perPage + i + 1,
            name: u.name,
            username: u.username,
            role: u.role,
            more: u.id,
            fullData: u, // Store full user data for edit
        }));
        
        loading.value = false;
        return { data: transformedData, total };
    } catch (error) {
        console.error('Error fetching users:', error);
        loading.value = false;
        return { data: [], total: 0 };
    }
}

async function loadItems({ page, itemsPerPage: perPage }) {
    currentPage.value = page;
    itemsPerPage.value = perPage;
    const result = await fetchUsers(page, perPage);
    users.value = result.data;
}

async function fetchRoles() {
    return (await (await fetch('/api/roles')).json()).data;
}

async function deleteUser(id) {
    const response = await axios.delete(`/api/users/${id}`);
    if (response.status !== 200) {
        alert((response.data.message));
        return;
    }
    await loadItems({ page: currentPage.value, itemsPerPage: itemsPerPage.value });
}

function openEditDialog(user) {
    userEditForm.value = {
        id: user.more,
        name: user.name,
        username: user.username,
        role: user.role,
        password: null, // Password is optional for edit
    };
    showUserEditDialog.value = true;
}

function isCurrentUser(userId) {
    return currentUser.value && currentUser.value.id === userId;
}

function toggleShowPassword() {
    showPassword.value = !showPassword.value;
}

function toggleShowEditPassword() {
    showEditPassword.value = !showEditPassword.value;
}

async function submitUserAddForm() {
    const validated = await userAddFormRef.value.validate();
    if (validated.valid) {
        disableUsetAddSubmit.value = true;
        try {
            const response = await axios.post("/api/users", {
                name: userAddForm.value.name,
                username: userAddForm.value.username,
                password: userAddForm.value.password,
                role: userAddForm.value.role,
            });
            if (response.status != 201) {
                disableUsetAddSubmit.value = false;
                alert("Error: " + response.data.message);
                return;
            }
            disableUsetAddSubmit.value = false;
            showUserAddDialog.value = false;
            userAddFormRef.value.reset();
            await loadItems({ page: 1, itemsPerPage: itemsPerPage.value });
        } catch (error) {
            disableUsetAddSubmit.value = false;
            alert("Error: " + (error.response?.data?.message || error.message));
        }
    }
}

async function submitUserEditForm() {
    const validated = await userEditFormRef.value.validate();
    if (validated.valid) {
        disableUserEditSubmit.value = true;
        try {
            const payload = {
                name: userEditForm.value.name,
                username: userEditForm.value.username,
                role: userEditForm.value.role,
            };
            
            // Only include password if it's provided
            if (userEditForm.value.password) {
                payload.password = userEditForm.value.password;
            }
            
            const response = await axios.put(`/api/users/${userEditForm.value.id}`, payload);
            if (response.status != 200) {
                disableUserEditSubmit.value = false;
                alert("Error: " + response.data.message);
                return;
            }
            disableUserEditSubmit.value = false;
            showUserEditDialog.value = false;
            userEditFormRef.value.reset();
            await loadItems({ page: currentPage.value, itemsPerPage: itemsPerPage.value });
        } catch (error) {
            disableUserEditSubmit.value = false;
            alert("Error: " + (error.response?.data?.message || error.message));
        }
    }
}

onMounted(async function () {
    await loadItems({ page: 1, itemsPerPage: 10 });
    roles.value = await fetchRoles();
});
</script>

<template>
    <Head title="Pengguna"></Head>
    <v-container>
        <v-row>
            <v-col>
                <h1 class="text-4xl font-medium">Kelola <span class="text-blue-accent-3">Pengguna</span></h1>
            </v-col>
        </v-row>
        <v-row>
            <v-col>
                <v-btn variant="tonal" color="blue-darken-2">
                    <span>
                        <v-icon icon="mdi-account-plus" />
                        Tambah pengguna
                    </span>
                    <v-dialog activator="parent" max-width="800" v-slot="{ isActive }" v-model="showUserAddDialog">
                        <v-card>
                            <v-card-title class="text-center">Form Tambah Pengguna</v-card-title>
                            <v-divider />
                            <v-card-text>
                                <v-form ref="userAddFormRef">
                                    <v-text-field v-model="userAddForm.name" label="Nama Lengkap" density="comfortable"
                                        :rules="userAddFormRule.name" />
                                    <v-text-field v-model="userAddForm.username" label="Username" density="comfortable"
                                        :rules="userAddFormRule.username" />
                                    <v-text-field v-model="userAddForm.password" label="Password" density="comfortable"
                                        :rules="userAddFormRule.password" :type="showPassword ? 'text' : 'password'"
                                        :append-inner-icon="showPassword ? 'mdi-eye' : 'mdi-eye-off'"
                                        @click:append-inner="toggleShowPassword" />
                                    <v-select v-model="userAddForm.role" :items="roles" item-title="name"
                                        density="comfortable" :rules="userAddFormRule.role" item-value="name"
                                        label="Role" />
                                </v-form>
                            </v-card-text>
                            <v-card-actions>
                                <v-btn @click="isActive.value = false">Cancel</v-btn>
                                <v-btn color="blue-darken-4" :disabled="disableUsetAddSubmit"
                                    @click="submitUserAddForm">
                                    <span v-if="!disableUsetAddSubmit">Submit</span>
                                    <span v-else>
                                        <v-progress-circular indeterminate size="20" />
                                    </span>
                                </v-btn>
                            </v-card-actions>
                        </v-card>
                    </v-dialog>
                </v-btn>
            </v-col>
        </v-row>
        
        <!-- Edit User Dialog -->
        <v-dialog max-width="800" v-model="showUserEditDialog">
            <v-card>
                <v-card-title class="text-center">Form Sunting Pengguna</v-card-title>
                <v-divider />
                <v-card-text>
                    <v-form ref="userEditFormRef">
                        <v-text-field v-model="userEditForm.name" label="Nama Lengkap" density="comfortable"
                            :rules="userEditFormRule.name" />
                        <v-text-field v-model="userEditForm.username" label="Username" density="comfortable"
                            :rules="userEditFormRule.username" />
                        <v-text-field v-model="userEditForm.password" label="Password (kosongkan jika tidak diubah)" 
                            density="comfortable"
                            :rules="userEditFormRule.password" :type="showEditPassword ? 'text' : 'password'"
                            :append-inner-icon="showEditPassword ? 'mdi-eye' : 'mdi-eye-off'"
                            @click:append-inner="toggleShowEditPassword" />
                        <v-select v-model="userEditForm.role" :items="roles" item-title="name"
                            density="comfortable" :rules="userEditFormRule.role" item-value="name"
                            label="Role" />
                    </v-form>
                </v-card-text>
                <v-card-actions>
                    <v-btn @click="showUserEditDialog = false">Cancel</v-btn>
                    <v-btn color="blue-darken-4" :disabled="disableUserEditSubmit"
                        @click="submitUserEditForm">
                        <span v-if="!disableUserEditSubmit">Update</span>
                        <span v-else>
                            <v-progress-circular indeterminate size="20" />
                        </span>
                    </v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>
        
        <!-- Desktop Table View -->
        <v-row class="hidden! md:block!">
            <v-col>
                <v-data-table-server 
                    :headers="[
                        { title: 'No', key: 'no' },
                        { title: 'Nama', key: 'name' },
                        { title: 'Username', key: 'username' },
                        { title: 'Role', key: 'role' },
                        { title: 'More', key: 'more' }
                    ]" 
                    :items="users"
                    :items-length="totalItems"
                    :loading="loading"
                    @update:options="loadItems"
                    class="hidden! md:block!">
                    <template #headers="{ headers }">
                        <tr class="bg-blue-darken-2">
                            <th class="w-1/16">No</th>
                            <th>Nama</th>
                            <th>Username</th>
                            <th>Role</th>
                            <th class="w-1/12">Tindakan</th>
                        </tr>
                    </template>
                    <template #item.more="{ item }">
                        <v-btn variant="text" icon>
                            <v-icon icon="mdi-dots-vertical" />
                            <v-menu activator="parent">
                                <v-list density="compact">
                                    <v-list-item 
                                        value="edit" 
                                        @click="openEditDialog(item)"
                                        :disabled="isCurrentUser(item.more)">
                                        <v-icon icon="mdi-pencil" class="mr-2" />
                                        Sunting
                                    </v-list-item>
                                    <v-list-item 
                                        value="delete"
                                        :disabled="isCurrentUser(item.more)">
                                        <v-icon icon="mdi-delete" class="mr-2" />
                                        Hapus
                                        <v-dialog activator="parent" max-width="400" v-slot="{ isActive }">
                                            <v-card>
                                                <v-card-title
                                                    class="text-wrap text-center bg-blue-darken-2">Konfirmasi!</v-card-title>
                                                <v-card-text>
                                                    <div>Apakah yakin untuk menghapus pengguna dengan nama <span
                                                            class="text-blue-600">{{ item.name }}</span></div>
                                                </v-card-text>
                                                <v-card-actions>
                                                    <v-btn @click="deleteUser(item.more); isActive.value = false">Ya</v-btn>
                                                    <v-btn @click="isActive.value = false">Batal</v-btn>
                                                </v-card-actions>
                                            </v-card>
                                        </v-dialog>
                                    </v-list-item>
                                </v-list>
                            </v-menu>
                        </v-btn>
                    </template>
                </v-data-table-server>
            </v-col>
        </v-row>
        
        <!-- Mobile Card View -->
        <v-row class="md:hidden!">
            <v-col>
                <v-progress-circular v-if="loading" indeterminate class="mx-auto d-block my-4" />
                <v-row v-for="user in users" :key="user.more">
                    <v-col>
                        <v-card>
                            <v-card-actions class="flex justify-end bg-blue-darken-2">
                                <v-btn variant="text" icon>
                                    <v-icon icon="mdi-dots-vertical" />
                                    <v-menu activator="parent">
                                        <v-list density="compact">
                                            <v-list-item 
                                                value="edit" 
                                                @click="openEditDialog(user)"
                                                :disabled="isCurrentUser(user.more)">
                                                <v-icon icon="mdi-pencil" class="mr-2" />
                                                Sunting
                                            </v-list-item>
                                            <v-list-item 
                                                value="delete"
                                                :disabled="isCurrentUser(user.more)">
                                                <v-icon icon="mdi-delete" class="mr-2" />
                                                Hapus
                                                <v-dialog activator="parent" max-width="400" v-slot="{ isActive }">
                                                    <v-card>
                                                        <v-card-title
                                                            class="text-wrap text-center bg-blue-darken-2">Konfirmasi!</v-card-title>
                                                        <v-card-text>
                                                            <div>Apakah yakin untuk menghapus pengguna dengan nama <span
                                                                    class="text-blue-600">{{ user.name }}</span></div>
                                                        </v-card-text>
                                                        <v-card-actions>
                                                            <v-btn @click="deleteUser(user.more); isActive.value = false">Ya</v-btn>
                                                            <v-btn @click="isActive.value = false">Batal</v-btn>
                                                        </v-card-actions>
                                                    </v-card>
                                                </v-dialog>
                                            </v-list-item>
                                        </v-list>
                                    </v-menu>
                                </v-btn>
                            </v-card-actions>
                            <v-card-text>
                                <v-row>
                                    <v-col cols="5">Nama Lengkap</v-col>
                                    <v-col>{{ user.name }}</v-col>
                                </v-row>
                                <v-row>
                                    <v-col cols="5">Username</v-col>
                                    <v-col>{{ user.username }}</v-col>
                                </v-row>
                                <v-row>
                                    <v-col cols="5">Role</v-col>
                                    <v-col>{{ user.role }}</v-col>
                                </v-row>
                            </v-card-text>
                        </v-card>
                    </v-col>
                </v-row>
                
                <!-- Mobile Pagination -->
                <v-row v-if="!loading && users.length > 0">
                    <v-col>
                        <v-pagination
                            v-model="currentPage"
                            :length="Math.ceil(totalItems / itemsPerPage)"
                            @update:modelValue="loadItems({ page: currentPage, itemsPerPage })"
                            total-visible="5"
                        />
                    </v-col>
                </v-row>
            </v-col>
        </v-row>
    </v-container>
</template>