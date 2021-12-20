<template>
  <div>
    <label>{{ label }}</label>
    <div class="table-responsive file-upload">
      <table class="table table-hover">
        <thead>
          <tr>
            <th>#</th>
            <th>Thumb</th>
            <th>Name</th>
            <th>Status</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody
          v-cloak
          @drop.prevent="dragleave"
          @dragover.prevent
          @dragover="dragover"
          @dragleave="dragleave"
        >
          <tr v-if="!files.length">
            <td colspan="7">
              <div class="text-center p-5">
                No File, Drag and drop files here
              </div>
            </td>
          </tr>
          <tr v-for="(file, index) in files" :key="file.id">
            <td>{{ index + 1 }}</td>
            <td>
              <div v-if="checkIsPdf(file.name) != 'pdf'">
                <img
                  v-if="file.thumb"
                  :src="file.thumb"
                  width="40"
                  height="auto"
                />
                <img
                  v-else-if="file.view"
                  :src="file.view"
                  width="40"
                  height="auto"
                />
                <span v-else>No Image</span>
              </div>
            </td>
            <td>
              <div class="filename">
                <a :href="file.view ? file.view : file.blob" target="_blank">
                  {{ file.name }}
                </a>
              </div>
              <div
                class="progress"
                v-if="
                  file.progress != null &&
                  (file.active || file.progress !== '0.00')
                "
              >
                <div
                  :class="{
                    'progress-bar': true,
                    'progress-bar-striped': true,
                    'bg-danger': file.error,
                    'progress-bar-animated': file.active,
                  }"
                  role="progressbar"
                  :style="{ width: file.progress + '%' }"
                >
                  {{ file.progress }}%
                </div>
              </div>
            </td>

            <td v-if="file.error">
              {{ file.error }}
            </td>
            <td v-else-if="file.success">Success</td>
            <td v-else-if="file.active">Active</td>
            <td v-else>Uploaded</td>
            <td>
              <CButton color="danger" @click.prevent="removeFile(file)">
                Remove
              </CButton>
            </td>
          </tr>
        </tbody>
        <tfoot>
          <td colspan="5">
            <file-upload
              :drop="true"
              :input-id="'file-' + name"
              v-model="files"
              class="btn btn-primary btn-upload"
              :custom-action="postingAction"
              :ref="name"
              :multiple="true"
              @input-filter="inputFilter"
              @input-file="inputFile"
            >
              Upload File
            </file-upload>
            <div
              class="text-danger"
              style="margin-top: 2px"
              v-show="withValidation"
            >
              Note: Only file jpg, jpeg and pdf are allowed!
            </div>
          </td>
        </tfoot>
      </table>
    </div>
  </div>
</template>

<script>
export default {
  props: {
    name: {
      type: String,
    },
    url: {
      type: String,
    },
    label: {
      type: String,
      default: "Label",
    },
    type: {
      type: String,
    },
    withValidation: {
      type: Boolean,
      default: false,
    },
  },
  data() {
    return {
      files: [],
      file_paths: [],
    };
  },
  computed: {
    uploadDisabled() {
      return this.files.length === 0;
    },
  },
  methods: {
    dragover(event) {
      event.preventDefault();
      // Add some visual fluff to show the user can drop its files
      if (!event.currentTarget.classList.contains("dragover-on")) {
        event.currentTarget.classList.remove("dragover-off");
        event.currentTarget.classList.add("dragover-on");
      }
    },
    dragleave(event) {
      // Clean up
      event.currentTarget.classList.add("dragover-off");
      event.currentTarget.classList.remove("dragover-on");
    },
    removeFile(file) {
      this.$emit("remove-action", { file: file, file_paths: this.file_paths });
      this.$refs[this.name].remove(file);
      for (var z = 0; z < this.files.length; z++) {
        if (this.files[z].id == file.id) {
          this.files.splice(z, 1);
          break;
        }
      }
    },
    inputFilter(newFile, oldFile, prevent) {
      if (newFile && !oldFile) {
        if (/(\/|^)(Thumbs\.db|desktop\.ini|\..+)$/.test(newFile.name)) {
          return prevent();
        }
        if (/\.(php5?|html?|jsx?)$/i.test(newFile.name)) {
          return prevent();
        }
      }
      if (newFile && (!oldFile || newFile.file !== oldFile.file)) {
        // Create a blob field
        // 创建 blob 字段
        newFile.blob = "";
        let URL = window.URL || window.webkitURL;
        if (URL && URL.createObjectURL) {
          newFile.blob = URL.createObjectURL(newFile.file);
        }
        // Thumbnails
        // 缩略图
        newFile.thumb = "";
        if (newFile.blob && newFile.type.substr(0, 6) === "image/") {
          newFile.thumb = newFile.blob;
        }
      }
    },
    inputFile(newFile, oldFile) {
      let ref = this.$refs[this.name];
      if (newFile && oldFile) {
        // update
        if (newFile.active && !oldFile.active) {
          // beforeSend
          // min size
          if (
            newFile.size >= 0 &&
            this.minSize > 0 &&
            newFile.size < this.minSize
          ) {
            ref.update(newFile, { error: "size" });
          }
        }
        if (newFile.progress !== oldFile.progress) {
          // progress
        }
        if (newFile.error && !oldFile.error) {
          // error
          newFile.error = "Failed to upload file.";
        }
        if (newFile.success && !oldFile.success) {
          // success
        }
      }
      if (!newFile && oldFile) {
        // remove
        if (oldFile.success && oldFile.response.id) {
          // $.ajax({
          //   type: 'DELETE',
          //   url: '/upload/delete?id=' + oldFile.response.id,
          // })
        }
      }
      ref.active = true;
    },
    checkIsPdf(filename) {
      var fileExt = filename.split(".").pop();
      return fileExt;
    },
    async postingAction(file, component) {
      return this.customAction(file, component, this.type);
    },
    customAction(file, component, type) {
      var formData = new FormData();
      formData.append("file", file.file);
      formData.append("type", type);
      return axios({
        method: "post",
        url: this.url,
        data: formData,
      }).then((response) => {
        this.file_paths.push({
          id: file.id,
          name: file.name,
          url: response.data.url,
          type: type,
        });
      });
    },
  },
};
</script>

<style >
.dragover-on {
  background-color: #e4e5e6;
}

.dragover-off {
  background-color: white;
}
</style>
