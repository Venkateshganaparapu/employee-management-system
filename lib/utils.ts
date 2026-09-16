import { clsx, type ClassValue } from "clsx";
import { twMerge } from "tailwind-merge";

export function cn(...inputs: ClassValue[]) {
  return twMerge(clsx(inputs));
}

export function formatCurrency(amount: number, currency = "USD") {
  return new Intl.NumberFormat("en-US", {
    style: "currency",
    currency,
    minimumFractionDigits: 0,
  }).format(amount);
}

export function formatDate(date: Date | string) {
  return new Intl.DateTimeFormat("en-US", {
    year: "numeric",
    month: "short",
    day: "numeric",
  }).format(new Date(date));
}

export function calculateDays(startDate: Date, endDate: Date): number {
  const diffTime = Math.abs(endDate.getTime() - startDate.getTime());
  return Math.ceil(diffTime / (1000 * 60 * 60 * 24));
}

export function slugify(text: string) {
  return text.toLowerCase().replace(/\s+/g, "-").replace(/[^\w-]+/g, "");
}

export const CATEGORY_LABELS: Record<string, string> = {
  ALL: "All Cars",
  SEDAN: "Sedan",
  SUV: "SUV",
  SPORTS: "Sports",
  LUXURY: "Luxury",
  ELECTRIC: "Electric",
  TRUCK: "Truck",
  VAN: "Van",
  CONVERTIBLE: "Convertible",
};

export const CATEGORY_COLORS: Record<string, string> = {
  SEDAN: "bg-blue-500/10 text-blue-400 border-blue-500/20",
  SUV: "bg-green-500/10 text-green-400 border-green-500/20",
  SPORTS: "bg-red-500/10 text-red-400 border-red-500/20",
  LUXURY: "bg-purple-500/10 text-purple-400 border-purple-500/20",
  ELECTRIC: "bg-teal-500/10 text-teal-400 border-teal-500/20",
  TRUCK: "bg-yellow-500/10 text-yellow-400 border-yellow-500/20",
  VAN: "bg-orange-500/10 text-orange-400 border-orange-500/20",
  CONVERTIBLE: "bg-pink-500/10 text-pink-400 border-pink-500/20",
};
