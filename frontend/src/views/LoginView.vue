<template>
  <v-container class="fill-height">
    <v-row align="center" justify="center">
      <v-col cols="12" sm="8" md="4">
        <v-card>
          <v-card-title class="text-h5">
            Login
          </v-card-title>
          <v-card-text>
            <v-form>
              <v-text-field
                label="Email"
                type="email"
                variant="outlined"
              ></v-text-field>
              <v-text-field
                label="Password"
                type="password"
                variant="outlined"
              ></v-text-field>
            </v-form>
          </v-card-text>
          <v-card-actions>
            <v-spacer></v-spacer>
            <v-btn color="primary">
              Login
            </v-btn>
          </v-card-actions>
        </v-card>
      </v-col>
    </v-row>
  </v-container>
</template>

<script setup lang="ts">
// Login logic will be added here
</script>
