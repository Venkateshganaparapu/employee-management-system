"use client";

import { useState, useEffect } from "react";
import Link from "next/link";
import { usePathname } from "next/navigation";
import { motion, AnimatePresence } from "framer-motion";
import { Car, Menu, X, User, LayoutDashboard, LogOut, ChevronDown } from "lucide-react";
import { cn } from "@/lib/utils";

const NAV_LINKS = [
  { href: "/", label: "Home" },
  { href: "/cars", label: "Browse Cars" },
  { href: "/account/bookings", label: "My Bookings" },
];

const ADMIN_LINKS = [
  { href: "/admin", label: "Dashboard", icon: LayoutDashboard },
  { href: "/admin/cars", label: "Manage Cars", icon: Car },
  { href: "/admin/bookings", label: "Bookings", icon: User },
];

// Mock auth state — replace with useSession() from next-auth
const MOCK_USER = { name: "Alex Rivera", role: "ADMIN", image: null };

export function Navbar() {
  const pathname = usePathname();
  const [mobileOpen, setMobileOpen] = useState(false);
  const [scrolled, setScrolled] = useState(false);
  const [userMenuOpen, setUserMenuOpen] = useState(false);

  // Track scroll for background blur
  useEffect(() => {
    const onScroll = () => setScrolled(window.scrollY > 20);
    window.addEventListener("scroll", onScroll, { passive: true });
    return () => window.removeEventListener("scroll", onScroll);
  }, []);

  // Close mobile menu on route change
  useEffect(() => {
    setMobileOpen(false);
    setUserMenuOpen(false);
  }, [pathname]);

  const isAdmin = MOCK_USER.role === "ADMIN";

  return (
    <>
      <motion.header
        initial={{ y: -80, opacity: 0 }}
        animate={{ y: 0, opacity: 1 }}
        transition={{ duration: 0.5, ease: "easeOut" }}
        className={cn(
          "fixed top-0 left-0 right-0 z-50 transition-all duration-300",
          scrolled
            ? "bg-dark-950/90 backdrop-blur-xl border-b border-white/5 shadow-xl shadow-black/20"
            : "bg-transparent"
        )}
      >
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div className="flex items-center justify-between h-16 md:h-18">
            {/* Logo */}
            <Link href="/" className="flex items-center gap-2 group">
              <motion.div
                whileHover={{ rotate: 10, scale: 1.1 }}
                transition={{ type: "spring", stiffness: 300 }}
                className="w-8 h-8 bg-brand-500 rounded-lg flex items-center justify-center"
              >
                <Car className="w-5 h-5 text-white" />
              </motion.div>
              <span className="font-display text-xl font-bold text-white">
                Drive<span className="text-brand-500">Elite</span>
              </span>
            </Link>

            {/* Desktop Nav */}
            <nav className="hidden md:flex items-center gap-1">
              {NAV_LINKS.map((link) => {
                const active = pathname === link.href;
                return (
                  <Link
                    key={link.href}
                    href={link.href}
                    className={cn(
                      "relative px-4 py-2 rounded-lg text-sm font-medium transition-colors duration-200",
                      active ? "text-white" : "text-dark-300 hover:text-white"
                    )}
                  >
                    {active && (
                      <motion.span
                        layoutId="nav-active"
                        className="absolute inset-0 bg-white/8 rounded-lg"
                        transition={{ type: "spring", bounce: 0.2, duration: 0.4 }}
                      />
                    )}
                    <span className="relative z-10">{link.label}</span>
                  </Link>
                );
              })}
              {isAdmin && (
                <Link
                  href="/admin"
                  className={cn(
                    "relative px-4 py-2 rounded-lg text-sm font-medium transition-colors duration-200",
                    pathname.startsWith("/admin") ? "text-brand-400" : "text-dark-300 hover:text-brand-400"
                  )}
                >
                  Admin
                </Link>
              )}
            </nav>

            {/* Desktop Right */}
            <div className="hidden md:flex items-center gap-3">
              <Link href="/cars" className="btn-primary text-sm py-2 px-4">
                Rent a Car
              </Link>

              {/* User Menu */}
              <div className="relative">
                <button
                  onClick={() => setUserMenuOpen(!userMenuOpen)}
                  className="flex items-center gap-2 px-3 py-2 rounded-xl bg-dark-800 border border-dark-700 hover:border-dark-600 transition-colors"
                >
                  <div className="w-7 h-7 rounded-full bg-brand-500/20 border border-brand-500/40 flex items-center justify-center text-brand-400 text-xs font-bold">
                    {MOCK_USER.name.charAt(0)}
                  </div>
                  <span className="text-sm text-dark-200">{MOCK_USER.name.split(" ")[0]}</span>
                  <ChevronDown className={cn("w-4 h-4 text-dark-400 transition-transform", userMenuOpen && "rotate-180")} />
                </button>

                <AnimatePresence>
                  {userMenuOpen && (
                    <motion.div
                      initial={{ opacity: 0, y: 8, scale: 0.95 }}
                      animate={{ opacity: 1, y: 0, scale: 1 }}
                      exit={{ opacity: 0, y: 8, scale: 0.95 }}
                      transition={{ duration: 0.15 }}
                      className="absolute right-0 top-full mt-2 w-52 bg-dark-800 border border-dark-700 rounded-xl shadow-2xl overflow-hidden"
                    >
                      <div className="p-3 border-b border-dark-700">
                        <p className="text-sm font-semibold text-white">{MOCK_USER.name}</p>
                        <p className="text-xs text-dark-400 mt-0.5">Premium Member</p>
                      </div>
                      <div className="p-1.5">
                        <Link href="/account" className="flex items-center gap-2.5 px-3 py-2 text-sm text-dark-200 hover:text-white hover:bg-white/5 rounded-lg transition-colors">
                          <User className="w-4 h-4" />
                          My Account
                        </Link>
                        {isAdmin && ADMIN_LINKS.map((l) => (
                          <Link key={l.href} href={l.href} className="flex items-center gap-2.5 px-3 py-2 text-sm text-dark-200 hover:text-white hover:bg-white/5 rounded-lg transition-colors">
                            <l.icon className="w-4 h-4" />
                            {l.label}
                          </Link>
                        ))}
                        <div className="my-1 border-t border-dark-700" />
                        <button className="flex w-full items-center gap-2.5 px-3 py-2 text-sm text-red-400 hover:text-red-300 hover:bg-red-500/5 rounded-lg transition-colors">
                          <LogOut className="w-4 h-4" />
                          Sign Out
                        </button>
                      </div>
                    </motion.div>
                  )}
                </AnimatePresence>
              </div>
            </div>

            {/* Mobile Menu Toggle */}
            <button
              onClick={() => setMobileOpen(!mobileOpen)}
              className="md:hidden p-2 rounded-lg text-dark-300 hover:text-white hover:bg-white/5 transition-colors"
            >
              {mobileOpen ? <X className="w-5 h-5" /> : <Menu className="w-5 h-5" />}
            </button>
          </div>
        </div>
      </motion.header>

      {/* Mobile Menu */}
      <AnimatePresence>
        {mobileOpen && (
          <motion.div
            initial={{ opacity: 0, x: "100%" }}
            animate={{ opacity: 1, x: 0 }}
            exit={{ opacity: 0, x: "100%" }}
            transition={{ type: "spring", bounce: 0, duration: 0.35 }}
            className="fixed inset-0 z-40 bg-dark-950 pt-16 overflow-y-auto"
          >
            <nav className="p-6 space-y-2">
              {NAV_LINKS.map((link, i) => (
                <motion.div
                  key={link.href}
                  initial={{ opacity: 0, x: 20 }}
                  animate={{ opacity: 1, x: 0 }}
                  transition={{ delay: i * 0.06 }}
                >
                  <Link
                    href={link.href}
                    className={cn(
                      "block px-4 py-3 rounded-xl text-lg font-medium transition-colors",
                      pathname === link.href
                        ? "bg-brand-500/10 text-brand-400"
                        : "text-dark-200 hover:text-white hover:bg-white/5"
                    )}
                  >
                    {link.label}
                  </Link>
                </motion.div>
              ))}
              {isAdmin && (
                <motion.div
                  initial={{ opacity: 0, x: 20 }}
                  animate={{ opacity: 1, x: 0 }}
                  transition={{ delay: 0.2 }}
                >
                  <Link href="/admin" className="block px-4 py-3 rounded-xl text-lg font-medium text-brand-400 hover:bg-brand-500/10 transition-colors">
                    Admin Panel
                  </Link>
                </motion.div>
              )}
              <motion.div initial={{ opacity: 0, x: 20 }} animate={{ opacity: 1, x: 0 }} transition={{ delay: 0.25 }} className="pt-4">
                <Link href="/cars" className="btn-primary w-full justify-center">
                  Rent a Car
                </Link>
              </motion.div>
            </nav>
          </motion.div>
        )}
      </AnimatePresence>
    </>
  );
}
