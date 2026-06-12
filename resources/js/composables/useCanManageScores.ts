import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

export function useCanManageScores() {
    const page = usePage();

    return computed(() => {
        const userId = page.props.auth?.user?.id;
        const adminId = page.props.scoresAdminUserId;

        if (userId == null || adminId == null) {
            return false;
        }

        return Number(userId) === Number(adminId);
    });
}
