<script setup>
import ApplicationLayout from '@/layouts/ApplicationLayout.vue';
import { ref, computed } from 'vue';
import { Head, usePage, router } from '@inertiajs/vue3';
import PageTitleHighlightPart from '@/components/atoms/PageTitleHighlightPart.vue';

defineOptions({
  layout: ApplicationLayout
});

const props = defineProps({
  recipients: Array,
});

const page = usePage();

const recipientAddForm = ref({
  name: null,
  nickname: null,
  division: null,
});
const recipientAddFormRef = ref(null);
const disableRecipientAddSubmit = ref(false);
const showRecipientAddDialog = ref(false);

// Edit recipient states
const recipientEditForm = ref({
  id: null,
  name: null,
  nickname: null,
  division: null,
});
const recipientEditFormRef = ref(null);
const disableRecipientEditSubmit = ref(false);
const showRecipientEditDialog = ref(false);

const recipientAddFormRule = {
  name: [
    requiredRule,
    (value) => value?.length >= 3 || "Nama harus setidaknya 3 karakter",
    (value) => value?.length < 255 || "Nama tidak boleh lebih 255 karakter",
  ],
  nickname: [
    // requiredRule,
    (value) => (value?.trim() && value?.length >= 2) ? "Nama panggilan harus setidaknya 2 karakter" : true,
    (value) => (value?.trim() && value?.length < 255) ? "Nama panggilan tidak boleh lebih 255 karakter" : true,
  ],
  division: [
    // 
  ],
};

const recipientEditFormRule = {
  name: [
    requiredRule,
    (value) => value?.length >= 3 || "Nama harus setidaknya 3 karakter",
    (value) => value?.length < 255 || "Nama tidak boleh lebih 255 karakter",
  ],
  nickname: [
    // requiredRule,
    (value) => (!!value?.length && value?.length >= 2) ? "Nama panggilan harus setidaknya 2 karakter" : true,
    (value) => (!!value?.length && value?.length < 255) ? "Nama panggilan tidak boleh lebih 255 karakter" : true,
  ],
  division: [
    // nggak ada validasi
  ],
};

function requiredRule(value) {
  return (!!value) || "Harus diisi";
}

const recipientsData = computed(() => {
  return (props.recipients || []).map((r, i) => ({
    no: i + 1,
    name: r.name,
    nickname: r.nickname,
    division: r.division || '-',
    more: r.id,
    fullData: r,
  }));
});

function deleteRecipient(id) {
  router.delete(`/recipients/${id}`);
}

function openEditDialog(recipient) {
  recipientEditForm.value = {
    id: recipient.more,
    name: recipient.name,
    nickname: recipient.nickname,
    division: recipient.division === '-' ? null : recipient.division,
  };
  showRecipientEditDialog.value = true;
}

async function submitRecipientAddForm() {
  const validated = await recipientAddFormRef.value.validate();
  if (validated.valid) {
    disableRecipientAddSubmit.value = true;

    router.post('/recipients', {
      name: recipientAddForm.value.name,
      nickname: recipientAddForm.value.nickname,
      division: recipientAddForm.value.division,
    }, {
      onSuccess: () => {
        disableRecipientAddSubmit.value = false;
        showRecipientAddDialog.value = false;
        recipientAddFormRef.value.reset();
      },
      onError: () => {
        disableRecipientAddSubmit.value = false;
      }
    });
  }
}

async function submitRecipientEditForm() {
  const validated = await recipientEditFormRef.value.validate();
  if (validated.valid) {
    disableRecipientEditSubmit.value = true;

    router.put(`/recipients/${recipientEditForm.value.id}`, {
      name: recipientEditForm.value.name,
      nickname: recipientEditForm.value.nickname,
      division: recipientEditForm.value.division,
    }, {
      onSuccess: () => {
        disableRecipientEditSubmit.value = false;
        showRecipientEditDialog.value = false;
        recipientEditFormRef.value.reset();
      },
      onError: () => {
        disableRecipientEditSubmit.value = false;
      }
    });
  }
}
</script>

<template>

  <Head title="Penerima"></Head>
  <v-container>
    <v-row>
      <v-col>
        <PageTitleHighlightPart first-part-title="Kelola" second-part-title="Penerima" />
      </v-col>
    </v-row>
    <v-row>
      <v-col>
        <v-btn variant="tonal" color="blue-darken-2">
          <span>
            <v-icon icon="mdi-account-multiple-plus" />
            Tambah penerima
          </span>
          <v-dialog v-slot="{ isActive }" v-model="showRecipientAddDialog" activator="parent" max-width="800">
            <v-card>
              <v-card-title class="text-center">Form Tambah Penerima</v-card-title>
              <v-divider />
              <v-card-text>
                <v-form ref="recipientAddFormRef">
                  <v-text-field v-model="recipientAddForm.name" label="Nama Lengkap" density="comfortable"
                    :rules="recipientAddFormRule.name" />
                  <v-text-field v-model="recipientAddForm.nickname" label="Nama Panggilan (Opsional)"
                    density="comfortable" :rules="recipientAddFormRule.nickname" />
                  <v-text-field v-model="recipientAddForm.division" density="comfortable"
                    :rules="recipientAddFormRule.division" label="Divisi (Opsional)" />
                </v-form>
              </v-card-text>
              <v-card-actions>
                <v-btn @click="isActive.value = false">Batal</v-btn>
                <v-btn color="blue-darken-4" :disabled="disableRecipientAddSubmit" @click="submitRecipientAddForm">
                  <span v-if="!disableRecipientAddSubmit">Simpan</span>
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

    <!-- Edit Recipient Dialog -->
    <v-dialog v-model="showRecipientEditDialog" max-width="800">
      <v-card>
        <v-card-title class="text-center">Form Sunting Penerima</v-card-title>
        <v-divider />
        <v-card-text>
          <v-form ref="recipientEditFormRef">
            <v-text-field v-model="recipientEditForm.name" label="Nama Lengkap" density="comfortable"
              :rules="recipientEditFormRule.name" />
            <v-text-field v-model="recipientEditForm.nickname" label="Nama Panggilan (Opsional)" density="comfortable"
              :rules="recipientEditFormRule.nickname" />
            <v-select v-model="recipientEditForm.division" density="comfortable" :rules="recipientEditFormRule.division"
              label="Divisi (Opsional)" clearable />
          </v-form>
        </v-card-text>
        <v-card-actions>
          <v-btn @click="showRecipientEditDialog = false">Batal</v-btn>
          <v-btn color="blue-darken-4" :disabled="disableRecipientEditSubmit" @click="submitRecipientEditForm">
            <span v-if="!disableRecipientEditSubmit">Perbarui</span>
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
        <v-data-table :headers="[
          { title: 'No', key: 'no' },
          { title: 'Nama Lengkap', key: 'name' },
          { title: 'Nama Panggilan', key: 'nickname' },
          { title: 'Divisi', key: 'division' },
          { title: 'Aksi', key: 'more' }
        ]" :items="recipientsData" class="hidden! md:block!">
          <template #headers="{ headers }">
            <tr class="bg-blue-darken-2">
              <th class="w-1/16">No</th>
              <th>Nama Lengkap</th>
              <th>Nama Panggilan</th>
              <th>Divisi</th>
              <th class="w-1/12">Tindakan</th>
            </tr>
          </template>
          <template #item.more="{ item }">
            <v-btn variant="text" icon>
              <v-icon icon="mdi-dots-vertical" />
              <v-menu activator="parent">
                <v-list density="compact">
                  <v-list-item value="edit" @click="openEditDialog(item)">
                    <v-icon icon="mdi-pencil" class="mr-2" />
                    Sunting
                  </v-list-item>
                  <v-list-item value="delete">
                    <v-icon icon="mdi-delete" class="mr-2" />
                    Hapus
                    <v-dialog v-slot="{ isActive }" activator="parent" max-width="400">
                      <v-card>
                        <v-card-title class="text-wrap text-center bg-blue-darken-2">Konfirmasi!</v-card-title>
                        <v-card-text>
                          <div>Apakah yakin untuk menghapus penerima dengan nama <span class="text-blue-600">{{
                            item.name }}</span>?</div>
                        </v-card-text>
                        <v-card-actions>
                          <v-btn @click="deleteRecipient(item.more); isActive.value = false">Ya</v-btn>
                          <v-btn @click="isActive.value = false">Batal</v-btn>
                        </v-card-actions>
                      </v-card>
                    </v-dialog>
                  </v-list-item>
                </v-list>
              </v-menu>
            </v-btn>
          </template>
        </v-data-table>
      </v-col>
    </v-row>

    <!-- Mobile Card View -->
    <v-row class="md:hidden!">
      <v-col>
        <v-row v-for="recipient in recipientsData" :key="recipient.more">
          <v-col>
            <v-card>
              <v-card-actions class="flex justify-end bg-blue-darken-2">
                <v-btn variant="text" icon>
                  <v-icon icon="mdi-dots-vertical" />
                  <v-menu activator="parent">
                    <v-list density="compact">
                      <v-list-item value="edit" @click="openEditDialog(recipient)">
                        <v-icon icon="mdi-pencil" class="mr-2" />
                        Sunting
                      </v-list-item>
                      <v-list-item value="delete">
                        <v-icon icon="mdi-delete" class="mr-2" />
                        Hapus
                        <v-dialog v-slot="{ isActive }" activator="parent" max-width="400">
                          <v-card>
                            <v-card-title class="text-wrap text-center bg-blue-darken-2">Konfirmasi!</v-card-title>
                            <v-card-text>
                              <div>Apakah yakin untuk menghapus penerima dengan nama <span class="text-blue-600">{{
                                recipient.name }}</span>?</div>
                            </v-card-text>
                            <v-card-actions>
                              <v-btn @click="deleteRecipient(recipient.more); isActive.value = false">Ya</v-btn>
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
                  <v-col>{{ recipient.name }}</v-col>
                </v-row>
                <v-row>
                  <v-col cols="5">Nama Panggilan</v-col>
                  <v-col>{{ recipient.nickname }}</v-col>
                </v-row>
                <v-row>
                  <v-col cols="5">Divisi</v-col>
                  <v-col>{{ recipient.division }}</v-col>
                </v-row>
              </v-card-text>
            </v-card>
          </v-col>
        </v-row>
      </v-col>
    </v-row>
  </v-container>
</template>