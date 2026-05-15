export const TEAMS = ['band', 'media', 'worship'] as const;

export type Team = (typeof TEAMS)[number];
