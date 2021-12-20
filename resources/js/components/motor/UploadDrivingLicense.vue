<template>
  <div>
    <label>Driver License (Front & Back)</label>
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
        <tbody>
          <tr v-if="!log_files.length">
            <td colspan="7">
              <div class="text-center p-5">No File</div>
            </td>
          </tr>
          <tr v-for="(file, index) in log_files" :key="file.id">
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
                <a :href="file.view ? file.view : file.blob" target="_blank">{{
                  file.name
                }}</a>
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

            <td v-if="file.error">{{ file.error }}</td>
            <td v-else-if="file.success">Success</td>
            <td v-else-if="file.active">Active</td>
            <td v-else>Uploaded</td>
            <td>
              <CButton color="danger" @click.prevent="removeLogFile(file)">
                Remove
              </CButton>
            </td>
          </tr>
        </tbody>
        <tfoot>
          <td colspan="5">
            <file-upload
              input-id="file-log"
              v-model="log_files"
              class="btn btn-primary btn-upload"
              :custom-action="logAction"
              ref="logs"
              :multiple="true"
              @input-filter="inputFilter"
              @input-file="inputLog"
            >
              Upload File
            </file-upload>
          </td>
        </tfoot>
      </table>
    </div>
  </div>
</template>


<script>
export default {
  data() {
    return {
      log_files: [],
      file_paths: [],
    };
  },
  methods: {
    checkIsPdf(filename) {
      var fileExt = filename.split(".").pop();
      return fileExt;
    },
    inputLog(newFile, oldFile) {
      this.inputFile(newFile, oldFile, this.$refs.logs);
    },
    inputFile(newFile, oldFile, ref) {
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
        }
        if (newFile.success && !oldFile.success) {
          // success
        }
      }
      if (!newFile && oldFile) {
        // remove
        if (oldFile.success && oldFile.response.id) {
        }
      }
      ref.active = true;
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
        newFile.blob = "";
        let URL = window.URL || window.webkitURL;
        if (URL && URL.createObjectURL) {
          newFile.blob = URL.createObjectURL(newFile.file);
        }

        newFile.thumb = "";
        if (newFile.blob && newFile.type.substr(0, 6) === "image/") {
          newFile.thumb = newFile.blob;
        }
      }
    },
    async logAction(file, component) {
      return this.customAction(file, component, "driving_license");
    },
    customAction(file, component, type) {
      var formData = new FormData();
      formData.append("file", file.file);
      formData.append("type", type);
      return axios({
        method: "post",
        url: "/api/motors/upload",
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
