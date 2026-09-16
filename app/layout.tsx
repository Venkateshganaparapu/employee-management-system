import type { Metadata } from "next";
import { Inter, Syne } from "next/font/google";
import "./globals.css";
import { Navbar } from "@/components/Navbar";
import { Footer } from "@/components/Footer";
import { Toaster } from "react-hot-toast";

const inter = Inter({
  subsets: ["latin"],
  variable: "--font-inter",
  display: "swap",
});

const syne = Syne({
  subsets: ["latin"],
  variable: "--font-syne",
  display: "swap",
});

export const metadata: Metadata = {
  title: {
    default: "DriveElite — Premium Car Rentals",
    template: "%s | DriveElite",
  },
  description:
    "Rent premium and luxury cars at unbeatable prices. BMW, Tesla, Porsche, Lamborghini and more — book in minutes.",
  keywords: ["car rental", "luxury cars", "premium rental", "BMW rental", "Tesla rental"],
  openGraph: {
    title: "DriveElite — Premium Car Rentals",
    description: "Rent the car of your dreams today.",
    type: "website",
  },
};

export default function RootLayout({
  children,
}: {
  children: React.ReactNode;
}) {
  return (
    <html lang="en" className={`${inter.variable} ${syne.variable}`}>
      <body className="bg-dark-950 text-white antialiased font-sans min-h-screen flex flex-col">
        <Toaster
          position="top-right"
          toastOptions={{
            style: {
              background: "#1e293b",
              color: "#f8fafc",
              border: "1px solid #334155",
            },
          }}
        />
        <Navbar />
        <main className="flex-1">{children}</main>
        <Footer />
      </body>
    </html>
  );
}
