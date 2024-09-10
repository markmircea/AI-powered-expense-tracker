<template>
  <HeaderComponent :auth="auth" />

  <div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold mb-6">{{ team.name }}</h1>

    <div v-if="successMessage" class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
      <span class="block sm:inline">{{ successMessage }}</span>
    </div>

    <div v-if="errorMessage" class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
      <span class="block sm:inline">{{ errorMessage }}</span>
    </div>

    <div v-if="canManageTeam" class="mb-8">
      <h2 class="text-2xl font-semibold mb-4">Team Settings</h2>
      <form @submit.prevent="updateTeam" class="max-w-md">
        <div class="mb-4">
          <label for="teamName" class="block text-sm font-medium text-gray-700">Team Name</label>
          <input
            type="text"
            id="teamName"
            v-model="updateTeamForm.name"
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
            required
          >
        </div>
        <button
          type="submit"
          class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700 transition duration-300 mr-2"
          :disabled="updateTeamForm.processing"
        >
          Update Team Name
        </button>
        <button
          type="button"
          @click="deleteTeam"
          class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700 transition duration-300"
        >
          Delete Team
        </button>
      </form>
    </div>

    <div class="mb-8">
      <h2 class="text-2xl font-semibold mb-4">Team Members</h2>
      <ul v-if="team.members.length" class="space-y-2">
        <li v-for="member in team.members" :key="member.id" class="flex items-center justify-between bg-white shadow rounded-lg p-4">
          <span>{{ member.name }} ({{ member.email }})</span>
          <div>
            <span v-if="member.id === team.owner_id" class="text-green-600 mr-2">Owner</span>
            <button
              v-if="canManageTeam && member.id !== team.owner_id"
              @click="removeMember(member)"
              class="text-red-600 hover:text-red-800 mr-2"
            >
              Remove
            </button>
            <button
              v-if="member.id === user.id && member.id !== team.owner_id"
              @click="leaveTeam"
              class="text-red-600 hover:text-red-800"
            >
              Leave Team
            </button>
          </div>
        </li>
      </ul>
      <p v-else class="text-gray-600">This team has no members yet.</p>
    </div>

    <div v-if="canManageTeam" class="mb-8">
      <h2 class="text-2xl font-semibold mb-4">Add Member</h2>
      <form @submit.prevent="addMember" class="max-w-md">
        <div class="mb-4">
          <label for="email" class="block text-sm font-medium text-gray-700">Member Email</label>
          <input
            type="email"
            id="email"
            v-model="addMemberForm.email"
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
            required
          >
        </div>

        <div v-if="addMemberForm.errors.email" class="text-red-600 text-sm mb-4">{{ addMemberForm.errors.email }}</div>

        <button
          type="submit"
          class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700 transition duration-300"
          :disabled="addMemberForm.processing"
        >
          Add Member
        </button>
      </form>
    </div>

    <a href="/teams" class="text-indigo-600 hover:text-indigo-800 mt-4 inline-block">
      Back to Teams
    </a>
  </div>
</template>

<script>
import { useForm, router } from '@inertiajs/vue3';
import HeaderComponent from '../../components/HeaderComponent.vue';
import { ref, watch } from 'vue';

export default {
  components: {
    HeaderComponent
  },
  props: {
    team: Object,
    canManageTeam: Boolean,
    user: Object,
    auth: Object,
    flash: Object,
  },
  setup(props) {
    const team = ref(props.team);
    const successMessage = ref('');
    const errorMessage = ref('');

    const addMemberForm = useForm({
      email: '',
    });

    const updateTeamForm = useForm({
      name: team.value.name,
    });

    const addMember = () => {
      addMemberForm.post(`/teams/${team.value.id}/add-member`, {
        preserveScroll: true,
        onSuccess: (page) => {
          addMemberForm.reset('email');
          team.value = page.props.team;
          successMessage.value = page.props.flash?.success || 'Member added successfully';
          errorMessage.value = '';
          setTimeout(() => {
            successMessage.value = '';
          }, 3000);
        },
        onError: (errors) => {
          errorMessage.value = errors.email || 'An error occurred while adding the member';
          setTimeout(() => {
            errorMessage.value = '';
          }, 3000);
        },
      });
    };

    const removeMember = (member) => {
      if (confirm(`Are you sure you want to remove ${member.name} from the team?`)) {
        router.post(`/teams/${team.value.id}/remove-member`, { user_id: member.id }, {
          preserveScroll: true,
          onSuccess: (page) => {
            team.value = page.props.team;
            successMessage.value = page.props.flash?.success || 'Member removed successfully';
            errorMessage.value = '';
            setTimeout(() => {
              successMessage.value = '';
            }, 3000);
          },
          onError: (errors) => {
            errorMessage.value = errors.message || 'An error occurred while removing the member';
            setTimeout(() => {
              errorMessage.value = '';
            }, 3000);
          },
        });
      }
    };

    const updateTeam = () => {
      updateTeamForm.put(`/teams/${team.value.id}`, {
        preserveScroll: true,
        onSuccess: (page) => {
          team.value = page.props.team;
          successMessage.value = page.props.flash?.success || 'Team updated successfully';
          errorMessage.value = '';
          setTimeout(() => {
            successMessage.value = '';
          }, 3000);
        },
        onError: (errors) => {
          errorMessage.value = errors.name || 'An error occurred while updating the team';
          setTimeout(() => {
            errorMessage.value = '';
          }, 3000);
        },
      });
    };

    const deleteTeam = () => {
      if (confirm('Are you sure you want to delete this team? This action cannot be undone.')) {
        router.delete(`/teams/${team.value.id}`, {
          preserveScroll: false,
          preserveState: false,
        });
      }
    };

    const leaveTeam = () => {
      if (confirm('Are you sure you want to leave this team?')) {
        router.post(`/teams/${team.value.id}/leave`, {}, {
          preserveScroll: false,
          preserveState: false,
        });
      }
    };

    watch(() => props.flash, (newFlash) => {
      if (newFlash?.success) {
        successMessage.value = newFlash.success;
        setTimeout(() => {
          successMessage.value = '';
        }, 3000);
      }
      if (newFlash?.error) {
        errorMessage.value = newFlash.error;
        setTimeout(() => {
          errorMessage.value = '';
        }, 3000);
      }
    }, { immediate: true, deep: true });

    return {
      team,
      addMemberForm,
      updateTeamForm,
      addMember,
      removeMember,
      updateTeam,
      deleteTeam,
      leaveTeam,
      successMessage,
      errorMessage,
    };
  },
};
</script>
