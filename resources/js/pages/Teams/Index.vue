<template>
          <HeaderComponent :auth="auth" />

    <div class="container mx-auto px-4 py-8">
      <h1 class="text-3xl font-bold mb-6">Your Teams</h1>

      <div class="mb-8">
        <h2 class="text-2xl font-semibold mb-4">Teams You Own</h2>
        <div v-if="ownedTeams.length" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
          <div v-for="team in ownedTeams" :key="team.id" class="bg-white shadow rounded-lg p-4">
            <h3 class="text-xl font-semibold mb-2">{{ team.name }}</h3>
            <p class="text-gray-600 mb-4">Owner: {{ team.owner.name }}</p>
            <a :href="`/teams/${team.id}`" class="text-indigo-600 hover:text-indigo-800">View Team</a>
          </div>
        </div>
        <p v-else class="text-gray-600">You don't own any teams yet.</p>
      </div>

      <div class="mb-8">
        <h2 class="text-2xl font-semibold mb-4">Teams You're a Member Of</h2>
        <div v-if="memberTeams.length" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
          <div v-for="team in memberTeams" :key="team.id" class="bg-white shadow rounded-lg p-4">
            <h3 class="text-xl font-semibold mb-2">{{ team.name }}</h3>
            <p class="text-gray-600 mb-4">Owner: {{ team.owner.name }}</p>
            <a :href="`/teams/${team.id}`" class="text-indigo-600 hover:text-indigo-800">View Team</a>
          </div>
        </div>
        <p v-else class="text-gray-600">You're not a member of any teams yet.</p>
      </div>

      <a href="/teams/create" class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700 transition duration-300">
        Create New Team
      </a>
    </div>
  </template>

  <script>
    import HeaderComponent from '../../components/HeaderComponent.vue';

  export default {
    components: {
        HeaderComponent
    },
    props: {
      ownedTeams: Array,
      memberTeams: Array,
      auth: Object,
    },


  };
  </script>
