import { FC, ReactNode, createContext, useContext, useEffect, useState } from "react";

interface Props {
  children: ReactNode,
  initialized: Date
};

const TIMEOUT_MS = 2000;

const context = createContext({
  isSlow: false,
  setIsSlow: (_isSlow: boolean) => { },
  quickLoadExpiration: new Date(Date.now() + TIMEOUT_MS)
});

export const useConnectionQuality = () => useContext(context);

export const ConnectionQualityProvider: FC<Props> =
  ({
    children,
    initialized
  }) => {
    const [isSlow, setIsSlow] = useState(false);
    const [quickLoadExpiration, setQuickLoadExpiration] = useState(
      new Date(Date.now() + TIMEOUT_MS)
    );
    useEffect(() => {
      const quickLoadExpiration = new Date(initialized.getTime() + TIMEOUT_MS);
      setQuickLoadExpiration(quickLoadExpiration);
      setIsSlow(quickLoadExpiration < new Date());
    }, [initialized]);

    return <context.Provider value={{ isSlow, setIsSlow, quickLoadExpiration }}>{children}</context.Provider>;
  };
