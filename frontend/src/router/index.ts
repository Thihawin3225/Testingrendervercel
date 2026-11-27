
import { createRouter, createWebHistory } from 'vue-router'
import axios from 'axios'

import AppLayout from '../layouts/AppLayout.vue'
import AdminLayout from '../layouts/AdminLayout.vue'

import { authApi } from '../modules/auth/api/authApi'
import { getToken, clearToken } from '../modules/auth/authToken'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),

  routes: [
    // PUBLIC ROUTES
    {
      path: '/login',
      name: 'login',
      component: () => import('../views/auth/LoginView.vue'),
      meta: {
        title: 'Login',
      },
    },
    {
      path: '/forgot-password',
      name: 'forgot-password',
      component: () => import('../auth/views/ForgotPasswordView.vue'),
      meta: {
        requiresAuth: false,
      },
    },

    // STUDENT ROUTES
    {
      path: '/',
      component: AppLayout,
      meta: {
        requiresAuth: true,
      },

      children: [
        {
          path: '',
          redirect: { name: 'home' },
        },
        {
          path: 'dashboard',
          name: 'home',
          component: () => import('../views/student/DashboardView.vue'),
          meta: {
            title: 'Dashboard',
          },
        },
        {
          path: 'profile',
          name: 'profile',
          component: () =>
            import('../features/profile/pages/MyProfilePage.vue'),
          meta: {
            title: 'My Profile',
          },
        },

        // PEER LEARNING ROUTES
        {
          path: 'peer-learning/requests',
          name: 'peer-learning-requests',
          component: () =>
            import('../features/peer-learning/pages/PeerLearningRequestsPage.vue'),
          meta: {
            title: 'My Learning Requests',
          },
        },
        {
          path: 'peer-learning/feed',
          name: 'learning-needs-feed',
          component: () =>
            import('../features/peer-learning/pages/LearningNeedsFeedPage.vue'),
          meta: {
            title: 'Learning Needs Feed',
          },
        },

        // STUDY MATERIAL ROUTES
        {
          path: 'study-materials',
          name: 'study-materials',
          component: () =>
            import('../features/study-materials/pages/StudyMaterialsPage.vue'),
          meta: {
            title: 'Study Materials',
          },
        },
        {
          path: 'study-materials/my-uploads',
          name: 'my-uploads',
          component: () =>
            import('../features/study-materials/pages/MyUploadsPage.vue'),
          meta: {
            title: 'My Uploads',
          },
        },
        {
          path: 'study-materials/bookmarks',
          name: 'bookmarked-materials',
          component: () =>
            import('../features/study-materials/pages/BookmarkedMaterialsPage.vue'),
          meta: {
            title: 'Bookmarked Materials',
          },
        },

        // MARKETPLACE ROUTES
        {
          path: 'marketplace',
          name: 'marketplace',
          component: () =>
            import('../features/marketplace/pages/MarketplacePage.vue'),
          meta: {
            title: 'Campus Marketplace',
            requiresAuth: true,
          },
        },
        {
          path: 'marketplace/:id',
          name: 'marketplace-detail',
          component: () =>
            import('../features/marketplace/pages/MarketplaceDetailPage.vue'),
          meta: {
            title: 'Listing Details',
            requiresAuth: true,
          },
        },
        {
          path: 'seller/requests',
          name: 'seller-requests',
          component: () =>
            import('../features/marketplace/pages/SellerRequestsPage.vue'),
          meta: {
            title: 'Seller Requests',
          },
        },
        {
          path: 'my-requests',
          name: 'my-requests',
          component: () =>
            import('../features/marketplace/pages/MyRequestsPage.vue'),
          meta: {
            title: 'My Requests',
          },
        },
        {
          path: 'marketplace/create',
          name: 'marketplace-create',
          component: () =>
            import('../features/marketplace/pages/CreateListingView.vue'),
          meta: {
            title: 'Create Listing',
            requiresAuth: true,
          },
        },
        {
          path: 'marketplace/saved',
          name: 'saved-listings',
          component: () =>
            import('../features/marketplace/pages/SavedListingsPage.vue'),
          meta: {
            title: 'Saved Listings',
            requiresAuth: true,
          },
        },
        {
          path: '/marketplace/:id/edit',
          name: 'marketplace-edit',
          component: () =>
            import('../features/marketplace/pages/EditListingPage.vue'),
          meta: {
            requiresAuth: true,
          },
        },
      ],
    },

    // ADMIN ROUTES
    {
      path: '/admin',
      component: AdminLayout,
      meta: {
        requiresAuth: true,
        requiresAdmin: true,
      },

      children: [
        {
          path: '',
          redirect: { name: 'admin-dashboard' },
        },
        {
          path: 'dashboard',
          name: 'admin-dashboard',
          component: () =>
            import('../features/admin/pages/AdminDashboardView.vue'),
          meta: {
            title: 'Admin Dashboard',
          },
        },
        {
          path: 'students',
          name: 'admin-students',
          component: () =>
            import('../features/admin/pages/AdminStudentsView.vue'),
          meta: {
            title: 'Student Management',
          },
        },
        {
          path: 'marketplace-categories',
          name: 'admin-marketplace-categories',
          component: () =>
            import('../features/admin/marketplace-category/pages/MarketplaceCategoriesView.vue'),
          meta: {
            title: 'Marketplace Categories',
          },
        },
        {
          path: 'academics',
          name: 'admin-academics',
          component: () =>
            import('../features/admin/pages/AdminAcademicManagementView.vue'),
          meta: {
            title: 'Academic Management',
          },
        },

        // ADMIN ACADEMIC MODULE ROUTES
        {
          path: 'academic',
          children: [
            {
              path: 'academic-years',
              name: 'admin-academic-years',
              component: () =>
                import('../features/academic/pages/AcademicYearsView.vue'),
              meta: {
                title: 'Academic Years',
              },
            },
            {
              path: 'year-levels',
              name: 'admin-year-levels',
              component: () =>
                import('../features/academic/pages/YearLevelsView.vue'),
              meta: {
                title: 'Year Levels',
              },
            },
            {
              path: 'semesters',
              name: 'admin-semesters',
              component: () =>
                import('../features/academic/pages/SemestersView.vue'),
              meta: {
                title: 'Semesters',
              },
            },
            {
              path: 'majors',
              name: 'admin-majors',
              component: () =>
                import('@/features/academic/pages/MajorsView.vue'),
              meta: {
                title: 'Majors',
              },
            },
            {
              path: 'subjects',
              name: 'admin-subjects',
              component: () =>
                import('@/features/academic/pages/SubjectsView.vue'),
              meta: {
                title: 'Subjects',
              },
            },
            {
              path: 'subject-offerings',
              name: 'admin-subject-offerings',
              component: () =>
                import('../features/academic/pages/SubjectOfferingsView.vue'),
              meta: {
                title: 'Subject Offerings',
              },
            },
            {
              path: 'student-enrollments',
              name: 'admin-student-enrollments',
              component: () =>
                import('@/features/academic/pages/StudentEnrollmentsView.vue'),
              meta: {
                title: 'Student Enrollments',
              },
            },
            {
              path: 'campus-locations',
              name: 'admin-campus-locations',
              component: () =>
                import('@/features/admin/campus-location/pages/CampusLocationView.vue'),
              meta: {
                title: 'Approved Locations',
              },
            },
          ],
        },
      ],
    },

    // UNKNOWN ROUTES
    {
      path: '/:pathMatch(.*)*',
      redirect: '/',
    },
  ],
})

// GLOBAL AUTHENTICATION AND AUTHORIZATION GUARD
router.beforeEach(async (to) => {
  const isLoginPage = to.name === 'login'

  const requiresAuth = to.matched.some(
    (record) => record.meta.requiresAuth === true,
  )

  const isAdminRoute = to.matched.some(
    (record) => record.meta.requiresAdmin === true,
  )

  // Allow public routes
  if (!requiresAuth && !isLoginPage) {
    return true
  }

  const token = getToken()

  // No token
  if (!token) {
    return isLoginPage
      ? true
      : {
          name: 'login',
          query: { redirect: to.fullPath },
          replace: true,
        }
  }

  try {
    // Validate token with Laravel
    const user = await authApi.me()

    // Validate account status and role
    const validRole = user.role === 'admin' || user.role === 'student'

    if (user.status !== 'active' || !validRole) {
      clearToken()

      return {
        name: 'login',
        replace: true,
      }
    }

    // Determine dashboard according to role
    const dashboard = user.role === 'admin' ? '/admin/dashboard' : '/dashboard'

    // Already authenticated users cannot access login
    if (isLoginPage) {
      return {
        path: dashboard,
        replace: true,
      }
    }

    // Students cannot access admin routes
    if (isAdminRoute && user.role !== 'admin') {
      return {
        path: '/dashboard',
        replace: true,
      }
    }

    // Admins use the admin area
    if (user.role === 'admin' && !isAdminRoute) {
      return {
        path: '/admin/dashboard',
        replace: true,
      }
    }

    return true
  } catch (error: unknown) {
    if (
      axios.isAxiosError(error) &&
      [401, 403].includes(error.response?.status ?? 0)
    ) {
      clearToken()
    }

    if (isLoginPage) {
      return true
    }

    return {
      name: 'login',
      replace: true,
    }
  }
})

export default router
