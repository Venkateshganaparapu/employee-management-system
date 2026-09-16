"use client";

import { motion } from "framer-motion";
import Link from "next/link";
import Image from "next/image";
import {
  User,
  Calendar,
  CreditCard,
  Settings,
  Car,
  Star,
  Clock,
  CheckCircle,
  XCircle,
  LogOut,
} from "lucide-react";
import { AnimatedPage, AnimatedSection } from "@/components/AnimatedPage";
import { MOCK_CARS } from "@/lib/mock-data";
import { cn, formatCurrency, formatDate } from "@/lib/utils";

// Mock booking history
const MOCK_BOOKINGS = [
  {
    id: "bk_001",
    carId: "car_001",
    startDate: "2026-08-10",
    endDate: "2026-08-14",
    days: 4,
    total: 1316,
    status: "COMPLETED",
    createdAt: "2026-08-01",
  },
  {
    id: "bk_002",
    carId: "car_002",
    startDate: "2026-09-15",
    endDate: "2026-09-18",
    days: 3,
    total: 824,
    status: "CONFIRMED",
    createdAt: "2026-09-07",
  },
  {
    id: "bk_003",
    carId: "car_007",
    startDate: "2026-07-01",
    endDate: "2026-07-03",
    days: 2,
    total: 196,
    status: "CANCELLED",
    createdAt: "2026-06-20",
  },
];

const STATUS_CONFIG = {
  PENDING:   { color: "text-yellow-400 bg-yellow-500/10 border-yellow-500/20", icon: Clock },
  CONFIRMED: { color: "text-blue-400 bg-blue-500/10 border-blue-500/20", icon: CheckCircle },
  ACTIVE:    { color: "text-brand-400 bg-brand-500/10 border-brand-500/20", icon: Car },
  COMPLETED: { color: "text-green-400 bg-green-500/10 border-green-500/20", icon: CheckCircle },
  CANCELLED: { color: "text-red-400 bg-red-500/10 border-red-500/20", icon: XCircle },
};

const MOCK_USER = { name: "Alex Rivera", email: "alex@example.com", phone: "+1 (555) 123-4567", memberSince: "2025-01-15" };

const TABS = [
  { id: "bookings", label: "My Bookings", icon: Calendar },
  { id: "profile", label: "Profile", icon: User },
  { id: "payment", label: "Payment Methods", icon: CreditCard },
];

export default function AccountPage() {
  return (
    <AnimatedPage className="min-h-screen pt-20">
      <div className="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        {/* Header */}
        <AnimatedSection className="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-10">
          <div className="flex items-center gap-4">
            <div className="w-16 h-16 rounded-2xl bg-brand-500/20 border border-brand-500/30 flex items-center justify-center text-brand-400 text-2xl font-bold">
              {MOCK_USER.name.charAt(0)}
            </div>
            <div>
              <h1 className="font-display text-2xl font-bold text-white">{MOCK_USER.name}</h1>
              <p className="text-dark-400 text-sm">{MOCK_USER.email} · Member since {formatDate(MOCK_USER.memberSince)}</p>
            </div>
          </div>
          <button className="flex items-center gap-2 text-sm text-red-400 hover:text-red-300 transition-colors">
            <LogOut className="w-4 h-4" /> Sign Out
          </button>
        </AnimatedSection>

        <div className="grid grid-cols-1 lg:grid-cols-4 gap-8">
          {/* Sidebar */}
          <AnimatedSection className="lg:col-span-1">
            <nav className="card p-3 space-y-1">
              {TABS.map((tab) => (
                <Link
                  key={tab.id}
                  href={`/account/${tab.id === "bookings" ? "bookings" : tab.id}`}
                  className="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium text-dark-300 hover:text-white hover:bg-white/5 transition-colors"
                >
                  <tab.icon className="w-4 h-4" />
                  {tab.label}
                </Link>
              ))}
            </nav>

            {/* Stats */}
            <div className="mt-4 card p-5 space-y-3">
              <h3 className="text-sm font-semibold text-white">Your Stats</h3>
              {[
                { label: "Total Bookings", value: MOCK_BOOKINGS.length },
                { label: "Cars Rented", value: new Set(MOCK_BOOKINGS.map(b => b.carId)).size },
                { label: "Total Spent", value: formatCurrency(MOCK_BOOKINGS.reduce((s, b) => s + b.total, 0)) },
              ].map((stat) => (
                <div key={stat.label} className="flex justify-between text-sm">
                  <span className="text-dark-400">{stat.label}</span>
                  <span className="text-white font-semibold">{stat.value}</span>
                </div>
              ))}
            </div>
          </AnimatedSection>

          {/* Main Content — Bookings */}
          <div className="lg:col-span-3 space-y-4">
            <AnimatedSection>
              <div className="flex items-center justify-between mb-4">
                <h2 className="font-display text-xl font-bold text-white">My Bookings</h2>
                <Link href="/cars" className="btn-outline text-sm py-2">
                  + New Booking
                </Link>
              </div>
            </AnimatedSection>

            {MOCK_BOOKINGS.map((booking, i) => {
              const car = MOCK_CARS.find(c => c.id === booking.carId);
              if (!car) return null;
              const status = STATUS_CONFIG[booking.status as keyof typeof STATUS_CONFIG];
              const StatusIcon = status.icon;

              return (
                <AnimatedSection key={booking.id} delay={i * 0.08}>
                  <div className="card overflow-hidden hover:border-dark-600 transition-colors">
                    <div className="flex flex-col sm:flex-row">
                      {/* Car image */}
                      <div className="relative w-full sm:w-44 h-36 sm:h-auto shrink-0">
                        <Image src={car.images[0]} alt={car.make} fill className="object-cover" />
                      </div>

                      {/* Details */}
                      <div className="p-5 flex-1 flex flex-col sm:flex-row items-start justify-between gap-4">
                        <div className="flex-1">
                          <div className="flex items-center gap-2 mb-1">
                            <h3 className="font-display font-bold text-white">{car.make} {car.model}</h3>
                          </div>
                          <p className="text-dark-400 text-sm mb-3">{car.year} · #{booking.id}</p>

                          <div className="flex flex-wrap gap-3 text-xs text-dark-400">
                            <span className="flex items-center gap-1">
                              <Calendar className="w-3.5 h-3.5" />
                              {booking.startDate} → {booking.endDate}
                            </span>
                            <span className="flex items-center gap-1">
                              <Clock className="w-3.5 h-3.5" />
                              {booking.days} day{booking.days > 1 ? "s" : ""}
                            </span>
                          </div>
                        </div>

                        <div className="flex flex-col items-end gap-3">
                          <span className={cn("badge border flex items-center gap-1.5", status.color)}>
                            <StatusIcon className="w-3.5 h-3.5" />
                            {booking.status.charAt(0) + booking.status.slice(1).toLowerCase()}
                          </span>
                          <p className="text-white font-bold">{formatCurrency(booking.total)}</p>
                          {booking.status === "COMPLETED" && (
                            <button className="text-xs text-brand-400 hover:text-brand-300 transition-colors flex items-center gap-1">
                              <Star className="w-3.5 h-3.5" /> Leave Review
                            </button>
                          )}
                        </div>
                      </div>
                    </div>
                  </div>
                </AnimatedSection>
              );
            })}
          </div>
        </div>
      </div>
    </AnimatedPage>
  );
}
