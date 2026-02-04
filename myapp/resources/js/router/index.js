import { createRouter, createWebHistory } from 'vue-router';
import { useAuthStore } from '../stores';

const routes = [
    {
        path: '/',
        redirect: '/login',
    },
    {
        path: '/login',
        name: 'login',
        component: () => import('../views/auth/LoginView.vue'),
        meta: { guest: true },
    },
    {
        path: '/register',
        name: 'register',
        component: () => import('../views/auth/RegisterView.vue'),
        meta: { guest: true },
    },
    {
        path: '/dashboard',
        name: 'user-dashboard',
        component: () => import('../views/user/DashboardView.vue'),
        meta: { requiresAuth: true, userOnly: true },
    },
    {
        path: '/appointments',
        name: 'appointments',
        component: () => import('../views/user/AppointmentsView.vue'),
        meta: { requiresAuth: true, userOnly: true },
    },
    {
        path: '/appointments/new',
        name: 'new-appointment',
        component: () => import('../views/user/NewAppointmentView.vue'),
        meta: { requiresAuth: true, userOnly: true },
    },
    {
        path: '/admin',
        name: 'admin-dashboard',
        component: () => import('../views/admin/DashboardView.vue'),
        meta: { requiresAuth: true, adminOnly: true },
    },
    {
        path: '/admin/appointments',
        name: 'admin-appointments',
        component: () => import('../views/admin/AppointmentsView.vue'),
        meta: { requiresAuth: true, adminOnly: true },
    },
    {
        path: '/admin/appointments/pending',
        name: 'admin-pending',
        component: () => import('../views/admin/PendingView.vue'),
        meta: { requiresAuth: true, adminOnly: true },
    },
    {
        path: '/admin/appointments/history',
        name: 'admin-history',
        component: () => import('../views/admin/HistoryView.vue'),
        meta: { requiresAuth: true, adminOnly: true },
    },
    {
        path: '/admin/time-slots',
        name: 'admin-time-slots',
        component: () => import('../views/admin/TimeSlotsView.vue'),
        meta: { requiresAuth: true, adminOnly: true },
    },
    {
        path: '/admin/disabled-dates',
        name: 'admin-disabled-dates',
        component: () => import('../views/admin/DisabledDatesView.vue'),
        meta: { requiresAuth: true, adminOnly: true },
    },
    {
        path: '/admin/conflicts',
        name: 'admin-conflicts',
        component: () => import('../views/admin/ConflictsView.vue'),
        meta: { requiresAuth: true, adminOnly: true },
    },
];

const router = createRouter({
    history: createWebHistory(),
    routes,
});

router.beforeEach((to, from, next) => {
    const authStore = useAuthStore();

    if (to.meta.requiresAuth && !authStore.isAuthenticated) {
        next({ name: 'login' });
        return;
    }

    if (to.meta.guest && authStore.isAuthenticated) {
        if (authStore.isAdmin) {
            next({ name: 'admin-dashboard' });
        } else {
            next({ name: 'user-dashboard' });
        }
        return;
    }

    if (to.meta.adminOnly && !authStore.isAdmin) {
        next({ name: 'user-dashboard' });
        return;
    }

    if (to.meta.userOnly && authStore.isAdmin) {
        next({ name: 'admin-dashboard' });
        return;
    }

    next();
});

export default router;
